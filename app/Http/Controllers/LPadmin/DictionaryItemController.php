<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use App\Models\LPadmin\Dictionary;
use App\Models\LPadmin\DictionaryItem;
use App\Services\LPadmin\DictionaryService;

class DictionaryItemController extends BaseController
{
    protected $dictionaryService;

    public function __construct(DictionaryService $dictionaryService)
    {
        $this->dictionaryService = $dictionaryService;
    }
    /**
     * 字典项管理页面
     */
    public function index(Dictionary $dictionary): View
    {
        return view('lpadmin.dictionary.items.index', compact('dictionary'));
    }

    /**
     * 获取字典项列表数据（AJAX）
     */
    public function select(Request $request, Dictionary $dictionary): JsonResponse
    {
        try {
            $query = $dictionary->items();

            // 搜索条件
            if ($request->filled('label')) {
                $query->where('label', 'like', '%' . $request->label . '%');
            }

            if ($request->filled('value')) {
                $query->where('value', 'like', '%' . $request->value . '%');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 排序
            $query->ordered();

            // 分页
            $items = $query->paginate($request->get('limit', 20));

            return $this->paginate($items);
        } catch (\Exception $e) {
            return $this->error('获取字典项列表失败：' . $e->getMessage());
        }
    }

    /**
     * 显示创建字典项页面
     */
    public function create(Dictionary $dictionary): View
    {
        return view('lpadmin.dictionary.items.create', compact('dictionary'));
    }

    /**
     * 保存新字典项
     */
    public function store(Request $request, Dictionary $dictionary): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:200',
            'value' => [
                'required',
                'string',
                'max:200',
                function ($attribute, $value, $fail) use ($dictionary) {
                    if ($dictionary->items()->where('value', $value)->exists()) {
                        $fail('该选项值已存在');
                    }
                },
            ],
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'sort' => 'nullable|integer|min:0',
            'status' => 'required|in:0,1',
        ], [
            'label.required' => '显示标签不能为空',
            'value.required' => '选项值不能为空',
            'status.required' => '状态不能为空',
        ]);

        if ($validator->fails()) {
            return $this->error('验证失败', $validator->errors());
        }

        try {
            $item = $dictionary->items()->create($request->only([
                'label', 'value', 'color', 'description', 'sort', 'status'
            ]));

            return $this->success($item, '字典项创建成功');
        } catch (\Exception $e) {
            return $this->error('字典项创建失败：' . $e->getMessage());
        }
    }

    /**
     * 显示字典项详情
     */
    public function show(Dictionary $dictionary, DictionaryItem $item): JsonResponse
    {
        try {
            // 确保字典项属于指定字典
            if ($item->dictionary_id !== $dictionary->id) {
                return $this->error('字典项不存在');
            }

            $item->load('dictionary');

            return $this->success($item);
        } catch (\Exception $e) {
            return $this->error('获取字典项详情失败：' . $e->getMessage());
        }
    }

    /**
     * 显示编辑字典项页面
     */
    public function edit(Dictionary $dictionary, DictionaryItem $item): View
    {
        // 确保字典项属于指定字典
        if ($item->dictionary_id !== $dictionary->id) {
            abort(404);
        }

        return view('lpadmin.dictionary.items.edit', compact('dictionary', 'item'));
    }

    /**
     * 更新字典项
     */
    public function update(Request $request, Dictionary $dictionary, DictionaryItem $item): JsonResponse
    {
        // 确保字典项属于指定字典
        if ($item->dictionary_id !== $dictionary->id) {
            return $this->error('字典项不存在');
        }

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:200',
            'value' => [
                'required',
                'string',
                'max:200',
                function ($attribute, $value, $fail) use ($dictionary, $item) {
                    if ($dictionary->items()->where('value', $value)->where('id', '!=', $item->id)->exists()) {
                        $fail('该选项值已存在');
                    }
                },
            ],
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'sort' => 'nullable|integer|min:0',
            'status' => 'required|in:0,1',
        ], [
            'label.required' => '显示标签不能为空',
            'value.required' => '选项值不能为空',
            'status.required' => '状态不能为空',
        ]);

        if ($validator->fails()) {
            return $this->error('验证失败', $validator->errors());
        }

        try {
            $item->update($request->only([
                'label', 'value', 'color', 'description', 'sort', 'status'
            ]));

            return $this->success($item, '字典项更新成功');
        } catch (\Exception $e) {
            return $this->error('字典项更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除字典项
     */
    public function destroy(Dictionary $dictionary, DictionaryItem $item): JsonResponse
    {
        try {
            // 确保字典项属于指定字典
            if ($item->dictionary_id !== $dictionary->id) {
                return $this->error('字典项不存在');
            }

            $item->delete();

            return $this->success(null, '字典项删除成功');
        } catch (\Exception $e) {
            return $this->error('字典项删除失败：' . $e->getMessage());
        }
    }

    /**
     * 批量删除字典项
     */
    public function batchDestroy(Request $request, Dictionary $dictionary): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->error('参数验证失败', $validator->errors());
        }

        try {
            $ids = $request->ids;
            
            $count = $dictionary->items()->whereIn('id', $ids)->delete();

            return $this->success(['count' => $count], "成功删除 {$count} 个字典项");
        } catch (\Exception $e) {
            return $this->error('批量删除失败：' . $e->getMessage());
        }
    }

    /**
     * 切换字典项状态
     */
    public function toggleStatus(Dictionary $dictionary, DictionaryItem $item): JsonResponse
    {
        try {
            // 确保字典项属于指定字典
            if ($item->dictionary_id !== $dictionary->id) {
                return $this->error('字典项不存在');
            }

            $item->status = $item->status === DictionaryItem::STATUS_ENABLED 
                          ? DictionaryItem::STATUS_DISABLED 
                          : DictionaryItem::STATUS_ENABLED;
            $item->save();

            return $this->success([
                'status' => $item->status,
                'status_label' => $item->status_label,
            ], '状态更新成功');
        } catch (\Exception $e) {
            return $this->error('状态更新失败：' . $e->getMessage());
        }
    }

    /**
     * 批量更新字典项排序
     */
    public function batchSort(Request $request, Dictionary $dictionary): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.sort' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->error('参数验证失败', $validator->errors());
        }

        try {
            foreach ($request->items as $itemData) {
                $dictionary->items()
                          ->where('id', $itemData['id'])
                          ->update(['sort' => $itemData['sort']]);
            }

            return $this->success(null, '排序更新成功');
        } catch (\Exception $e) {
            return $this->error('排序更新失败：' . $e->getMessage());
        }
    }
}
