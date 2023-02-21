<?php

namespace app\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MetadataItem;
use App\Models\Tag;
use App\Models\Tagging;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TaggingController extends Controller
{
    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        $tagging = Tagging::with([
            'metadataItems' => function (BelongsTo $query) {
                $query->where('metadata_type', '=', MetadataItem::METADATA_TYPE_MOVIE);
            },
            'tags' => function (BelongsTo $query) {
                $query->where('tag_type', '=', Tag::TAG_TYPE);
            }
        ])->findOrFail($id);

        return response()->json($tagging);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function getTagsByMetadataItemId(int $id): JsonResponse
    {
        $taggings = DB::table('taggings')
            ->selectRaw('taggings.id, taggings.tag_id, taggings.metadata_item_id, taggings.\'index\', tags.tag')
            ->where('taggings.metadata_item_id', '=', $id)
            ->join('tags', 'taggings.tag_id', '=', 'tags.id')
            ->where('tags.tag_type', '=', Tag::TAG_TYPE)
            ->orderBy('taggings.index', 'asc')
            ->get();

        if (!$taggings->count()) {
            abort(Response::HTTP_NOT_FOUND, 'Invalid Metadata Item ID');
        }

        return response()->json($taggings);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tag_id' => 'required|int|exists:tags,id',
            'metadata_item_id' => 'required|int|exists:metadata_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator, Response::HTTP_BAD_REQUEST);
        }

        // Just insert the new actor at the end for now, users can sort on the movie page
        $index = Tagging::with([
            'tags' => function (BelongsTo $query) {
                $query->where('tag_type', '=', Tag::TAG_TYPE);
            }
        ])->where('metadata_item_id', '=', $request->get('metadata_item_id'))
            ->max('index');

        ++$index;

        $tagging = Tagging::create([
            'tag_id' => $request->get('tag_id'),
            'metadata_item_id' => $request->get('metadata_item_id'),
            'tag_type' => Tag::TAG_TYPE,
            'index' => $index,
        ]);

        return response()->json($tagging);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateOrder(Request $request): JsonResponse
    {
        foreach ($request->get('billing') as $index => $id) {
            $tagging = Tagging::find($id);
            if (!$tagging instanceof Tagging) {
                abort(Response::HTTP_NOT_FOUND);
            }
            $tagging->index = $index;
            $tagging->save();
        }

        return response()->json(['status' => true]);
    }
}
