<?php

namespace App\Http\Controllers\BroadcastControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Models\Broadcasts\BroadcastAttachment;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use App\Repositories\Broadcast\BroadcastAttachment\IBroadcastAttachmentRepository;
use App\Repositories\System\Setting\ISettingRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Http\Redirector;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BroadcastController extends Controller {
  protected IBroadcastRepository $broadcastRepository;
  protected IBroadcastAttachmentRepository $broadcastAttachmentRepository;
  protected ISettingRepository $settingsRepository;

  public function __construct(
    IBroadcastRepository $broadcastRepository,
    ISettingRepository $settingRepository,
    IBroadcastAttachmentRepository $broadcastAttachmentRepository
  ) {
    $this->broadcastRepository = $broadcastRepository;
    $this->settingsRepository = $settingRepository;
    $this->broadcastAttachmentRepository = $broadcastAttachmentRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $broadcasts = $this->broadcastRepository->getAllBroadcastsOrderedByDate();
    $toReturnBroadcasts = [];
    foreach ($broadcasts as $broadcast) {
      $toReturnBroadcasts[] = $this->broadcastRepository->getBroadcastReturnable($broadcast);
    }

    return response()->json([
      'msg' => 'List of all broadcasts',
      'broadcasts' => $toReturnBroadcasts, ]);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function getSentReceiptReturnable(int $id) {
    $broadcast = $this->broadcastRepository->getBroadcastById($id);
    if ($broadcast == null) {
      return response()->json(['msg' => 'Broadcast not found'], 404);
    }

    return response()->json([
      'msg' => 'Get broadcast with send receipts',
      'broadcast' => $this->broadcastRepository->getBroadcastSentReceiptReturnable($broadcast), ]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, [
      'for_everyone' => 'required|boolean',
      'subject' => 'required|max:190|min:1',
      'bodyHTML' => 'required|string',
      'body' => 'required|string',
      'groups' => 'array',
      'groups.*' => 'required|integer',
      'subgroups' => 'array',
      'subgroups.*' => 'required|integer',
      'attachments' => 'array',
      'attachments.*' => 'required|integer',]);

    $forEveryone = $request->input('for_everyone');
    $subject = $request->input('subject');
    $bodyHTML = $request->input('bodyHTML');
    $body = $request->input('body');

    $groups = (array)$request->input('groups');
    $subgroups = (array)$request->input('subgroups');
    $attachments = (array)$request->input('attachments');

    $broadcast = $this->broadcastRepository->create(
      $subject,
      $bodyHTML,
      $body,
      $request->auth->id,
      $groups,
      $subgroups,
      $forEveryone,
      $attachments
    );

    if ($broadcast == null) {
      return response()->json(['msg' => 'Could not create broadcast'], 500);
    }

    return response()->json([
      'msg' => 'Successful created broadcast',
      'broadcast' => $broadcast, ], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete(Request $request, int $id) {
    $broadcast = $this->broadcastRepository->getBroadcastById($id);
    if ($broadcast == null) {
      return response()->json(['msg' => 'Broadcast not found'], 404);
    }

    if (! $this->broadcastRepository->delete($broadcast)) {
      Logging::error('deleteBroadcast', 'Could not delete broadcast! User id - ' . $request->auth->id);

      return response()->json(['msg' => 'Could not delete broadcast'], 500);
    }

    Logging::info('deleteBroadcast', 'Deleted broadcast! User id - ' . $request->auth->id);

    return response()->json(['msg' => 'Successfully deleted broadcast'], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function attachmentsUpload(Request $request) {
    $this->validate($request, ['files_count' => 'numeric']);

    $count = $request->input('files_count');

    $files = [];

    for ($i = 0; $i < $count; $i++) {
      $file = $request->file('file_' . $i);

      $path = $file->store('files');

      $fileModel = new BroadcastAttachment(['path' => $path, 'name' => $file->getClientOriginalName(), 'token' => $this->broadcastAttachmentRepository->getUniqueRandomBroadcastAttachmentToken()]);

      if (! $fileModel->save()) {
        Storage::delete($path);

        return response()->json(['msg' => 'Could not save files!'], 500);
      }
      $files[] = $fileModel;
    }

    return response()->json(['msg' => 'Files successfully uploaded!', 'files' => $files], 200);
  }

  /**
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function attachmentDelete(int $id) {
    $broadcastAttachment = $this->broadcastAttachmentRepository->getAttachmentById($id);
    if ($broadcastAttachment == null) {
      return response()->json(['msg' => 'Broadcast not found', 'error_code' => 'broadcast_attachment_not_found'], 404);
    }

    if ($this->broadcastAttachmentRepository->deleteAttachment($broadcastAttachment)) {
      return response()->json(['msg' => 'Broadcast attachment deleted successfully'], 200);
    } else {
      return response()->json(['msg' => 'Could not delete attachment'], 500);
    }
  }

  /**
   * @param string $token
   * @return RedirectResponse|Redirector|BinaryFileResponse
   */
  public function attachmentDownload(string $token) {
    $attachment = $this->broadcastAttachmentRepository->getAttachmentByToken($token);

    if ($attachment == null) {
      return redirect($this->settingsRepository->getUrl() . '/not-found');
    }

    $path = storage_path('app/' . $attachment->path);
    $type = File::mimeType($path);
    $headers = ['Content-Type' => $type];
    $response = response()->download($path, $attachment->name, $headers);
    ob_end_clean();

    return $response;
  }
}
