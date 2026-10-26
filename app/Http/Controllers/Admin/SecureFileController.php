<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepositProof;
use App\Models\GiftCard;
use App\Models\KycDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams privately-stored evidence files (KYC documents, bank-transfer
 * payment proofs, gift card photos) to admins only. These live on the
 * 'local' (non-public) disk on purpose — this is the only sanctioned way
 * to view them, so admins never had to be given direct filesystem/URL
 * access to user-uploaded documents.
 */
class SecureFileController extends Controller
{
    protected const MODELS = [
        'kyc-document' => KycDocument::class,
        'deposit-proof' => DepositProof::class,
        'gift-card-image' => GiftCard::class,
    ];

    public function show(string $type, int $id): StreamedResponse
    {
        abort_unless(array_key_exists($type, self::MODELS), 404);

        $model = self::MODELS[$type]::findOrFail($id);
        $path = $type === 'gift-card-image' ? $model->card_image : $model->file_path;

        abort_if(empty($path), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
