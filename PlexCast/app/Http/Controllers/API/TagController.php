<?php

namespace app\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MetadataItem;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tag' => 'required|unique:tags|max:255',
            'user_thumb_url' => 'url',
        ]);

        if ($validator->fails()) {
            return response()->json($validator, Response::HTTP_BAD_REQUEST);
        }

        $tag = Tag::create([
            'tag' => $request->get('tag'),
            'user_thumb_url' => $request->get('user_thumb_url'),
            'tag_type' => Tag::TAG_TYPE
        ]);

        return response()->json($tag);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tag' => 'required|unique:tags|max:255',
            'user_thumb_url' => 'url',
        ]);

        if ($validator->fails()) {
            return request()->json($validator, Response::HTTP_BAD_REQUEST);
        }

        $tag = Tag::findOrFail($id);
        $tag->tag = $request->get($tag);
        $tag->user_thumb_url = $request->get('user_thumb_url');
        $tag->save();

        return response()->json($tag);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        $tag = Tag::with([
            'tagging.metadataItems' => function (BelongsTo $query) {
                $query->where('metadata_type', '=', MetadataItem::METADATA_TYPE_MOVIE);
            }
        ])->findOrFail($id);

        return response()->json($tag);
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

        $tags = DB::table('tags')
            ->where('tag_type', Tag::TAG_TYPE)
            ->where('tag', 'LIKE', '%' . $request->get('name') . '%')
            ->orderBy('tag', 'ASC')
            ->get();

        return response()->json($tags);
    }
}
