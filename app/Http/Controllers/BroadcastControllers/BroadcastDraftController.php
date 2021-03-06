<?php

namespace App\Http\Controllers\BroadcastControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\Broadcast\BroadcastDraft\IBroadcastDraftRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BroadcastDraftController extends Controller {
  protected IBroadcastDraftRepository $draftRepository;

  public function __construct(IBroadcastDraftRepository $draftRepository) {
    $this->draftRepository = $draftRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll(): JsonResponse {
    return response()->json([
      'msg' => 'List of all broadcast drafts',
      'drafts' => $this->draftRepository->getAllBroadcastDraftsOrderedByDate(), ]);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(int $id): JsonResponse {
    $draft = $this->draftRepository->getBroadcastDraftById($id);
    if ($draft == null) {
      return response()->json(['msg' => 'Draft not found'], 404);
    }

    return response()->json([
      'msg' => 'Get broadcast draft',
      'draft' => $draft, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(AuthenticatedRequest $request): JsonResponse {
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
      'draft' => $draft, ], 201);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(AuthenticatedRequest $request, int $id): JsonResponse {
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
      'draft' => $draft, ], 201);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete(AuthenticatedRequest $request, int $id): JsonResponse {
    $draft = $this->draftRepository->getBroadcastDraftById($id);
    if ($draft == null) {
      return response()->json(['msg' => 'Draft not found'], 404);
    }

    if (! $this->draftRepository->delete($draft)) {
      Logging::error('deleteDraft', 'Could not delete draft! User id - ' . $request->auth->id);

      return response()->json(['msg' => 'Could not delete draft'], 500);
    }

    Logging::info('deleteDraft', 'Deleted draft! User id - ' . $request->auth->id);

    return response()->json(['msg' => 'Successfully deleted draft'], 200);
  }
}
