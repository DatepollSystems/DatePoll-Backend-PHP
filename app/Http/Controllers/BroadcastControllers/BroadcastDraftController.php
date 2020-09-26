<?php

namespace App\Http\Controllers\BroadcastControllers;

use App\Logging;
use App\Http\Controllers\Controller;
use App\Repositories\Broadcast\BroadcastDraft\IBroadcastDraftRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BroadcastDraftController extends Controller
{

  protected $draftRepository = null;

  public function __construct(IBroadcastDraftRepository $draftRepository) {
    $this->draftRepository = $draftRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $drafts = $this->draftRepository->getAllBroadcastDraftsOrderedByDate();
    $toReturnDrafts = array();
    foreach ($drafts as $draft) {
      $toReturnDrafts[] = $this->draftRepository->getBroadcastDraftReturnable($draft);
    }

    return response()->json([
      'msg' => 'List of all broadcast drafts',
      'drafts' => $toReturnDrafts]);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle($id) {
    $draft = $this->draftRepository->getBroadcastDraftById($id);
    if ($draft == null) {
      return response()->json(['msg' => 'Draft not found'], 404);
    }

    return response()->json([
      'msg' => 'Get broadcast draft',
      'draft' => $this->draftRepository->getBroadcastDraftReturnable($draft)]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, [
      'subject' => 'required|max:190|min:1',
      'bodyHTML' => 'required|string',
      'body' => 'required|string',]);

    $subject = $request->input('subject');
    $bodyHTML = $request->input('bodyHTML');
    $body = $request->input('body');

    $draft = $this->draftRepository->createOrUpdate($subject, $bodyHTML, $body, $request->auth->id);

    if ($draft == null) {
      return response()->json(['msg' => 'Could not create draft'], 500);
    }

    return response()->json([
      'msg' => 'Successful created draft',
      'draft' => $this->draftRepository->getBroadcastDraftReturnable($draft)], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(Request $request, int $id) {
    $this->validate($request, [
      'subject' => 'required|max:190|min:1',
      'bodyHTML' => 'required|string',
      'body' => 'required|string',]);

    $draft = $this->draftRepository->getBroadcastDraftById($id);
    if ($draft == null) {
      return response()->json(['msg' => 'Draft not found'], 404);
    }

    $subject = $request->input('subject');
    $bodyHTML = $request->input('bodyHTML');
    $body = $request->input('body');

    $draft = $this->draftRepository->createOrUpdate($subject, $bodyHTML, $body, $request->auth->id, $draft);

    if ($draft == null) {
      return response()->json(['msg' => 'Could not update draft'], 500);
    }

    return response()->json([
      'msg' => 'Successful updated draft',
      'draft' => $this->draftRepository->getBroadcastDraftReturnable($draft)], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete(Request $request, $id) {
    $draft = $this->draftRepository->getBroadcastDraftById($id);
    if ($draft == null) {
      return response()->json(['msg' => 'Draft not found'], 404);
    }

    if (!$this->draftRepository->delete($draft)) {
      Logging::error('deleteDraft', 'Could not delete draft! User id - ' . $request->auth->id);
      return response()->json(['msg' => 'Could not delete draft'], 500);
    }

    Logging::info('deleteDraft', 'Deleted draft! User id - ' . $request->auth->id);
    return response()->json(['msg' => 'Successfully deleted draft'], 200);
  }
}
