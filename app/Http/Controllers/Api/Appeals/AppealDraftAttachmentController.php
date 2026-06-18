<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Appeals;

use App\Application\Appeals\AppealDraftAccess;
use App\Application\Appeals\AppealDraftService;
use App\Application\Auth\AuthService;
use App\Http\Requests\Api\Appeals\AppealAttachmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class AppealDraftAttachmentController
{
    public function __construct(
        private AuthService $auth,
        private AppealDraftService $drafts,
    ) {}

    public function store(AppealAttachmentRequest $request, string $id): JsonResponse
    {
        $file = $request->file('file');

        if (! $file instanceof UploadedFile) {
            abort(422);
        }

        return response()->json([
            'data' => $this->drafts->addAttachment($this->draftAccess($request), $id, $file),
        ], 201);
    }

    public function destroy(Request $request, string $id, string $attachment): JsonResponse
    {
        $this->drafts->deleteAttachment($this->draftAccess($request), $id, $attachment);

        return response()->json(null, 204);
    }

    private function draftAccess(Request $request): AppealDraftAccess
    {
        return new AppealDraftAccess(
            user: $this->auth->userOrNull($request),
            guestToken: $request->headers->get('X-Appeal-Draft-Token'),
        );
    }
}
