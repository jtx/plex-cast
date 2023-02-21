<?php

namespace app\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MetadataItem;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MetadataItemController extends Controller
{
    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        $metadataItem = MetadataItem::with([
            'tagging.tags' => function (BelongsTo $query) {
                $query->where('tag_type', '=', Tag::TAG_TYPE);
            }
        ])->where('metadata_type', '=', MetadataItem::METADATA_TYPE_MOVIE)
            ->findOrFail($id);

        return response()->json($metadataItem);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $name = trim($request->get(('name')));
        if (strlen($name < 2)) {
            return response()->json([], Response::HTTP_NOT_FOUND);
        }

        $tags = DB::table('metadata_items')
            ->where('metadata_type', MetadataItem::METADATA_TYPE_MOVIE)
            ->where('title', 'LIKE', '%' . $request->get('name') . '%')
            ->orderBy('title', 'ASC')
            ->get();

        return response()->json($tags);
    }
}
