<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\LPadmin\Dictionary;
use App\Models\LPadmin\DictionaryItem;
use App\Services\LPadmin\DictionaryService;

class DictionaryController extends BaseController
{
    protected $dictionaryService;

    public function __construct(DictionaryService $dictionaryService)
    {
        $this->dictionaryService = $dictionaryService;
    }
    /**
     * 数据字典管理页面
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->expectsJson()) {
            $query = Dictionary::withCount('items');

            // 搜索条件
            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 排序
            $field = $request->get('field', 'id');
            $order = $request->get('order', 'desc');
            $query->orderBy($field, $order);

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 15);

            $dictionaries = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $data = [];
            foreach ($dictionaries->items() as $dictionary) {
                $item = [
                    'id' => $dictionary->id,
                    'name' => $this->cleanUtf8($dictionary->name),
                    'title' => $this->cleanUtf8($dictionary->title),
                    'type' => $dictionary->type,
                    'type_label' => $this->cleanUtf8($dictionary->type_label),
                    'description' => $this->cleanUtf8($dictionary->description ?? ''),
                    'items_count' => $dictionary->items_count,
                    'sort' => $dictionary->sort,
                    'status' => $dictionary->status,
                    'status_label' => $dictionary->status ? '启用' : '禁用',
                    'created_at' => $dictionary->created_at ? $dictionary->created_at->format('Y-m-d H:i:s') : '',
                    'updated_at' => $dictionary->updated_at ? $dictionary->updated_at->format('Y-m-d H:i:s') : '',
                ];
                $data[] = $item;
            }

            return response()->json([
                'code' => 0,
                'msg' => '',
                'count' => $dictionaries->total(),
                'data' => $data,
            ]);
        }

        return view('lpadmin.dictionary.index');
    }

    /**
     * 获取字典列表数据（AJAX）- 兼容旧接口
     */
    public function select(Request $request): JsonResponse
    {
        return $this->index($request);
    }



    /**
     * 显示创建字典页面
     */
    public function create(): View
    {
        return view('lpadmin.dictionary.create');
    }

    /**
     * 保存新字典
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:dictionaries,name',
            'title' => 'required|string|max:200',
            'type' => 'required|in:select,radio,checkbox',
            'description' => 'nullable|string',
            'sort' => 'nullable|integer|min:0',
            'status' => 'required|in:0,1',
        ], [
            'name.required' => '字典名称不能为空',
            'name.unique' => '字典名称已存在',
            'title.required' => '字典标题不能为空',
            'type.required' => '字典类型不能为空',
            'type.in' => '字典类型无效',
            'status.required' => '状态不能为空',
        ]);

        if ($validator->fails()) {
            return $this->error('验证失败', 422, $validator->errors());
        }

        try {
            $dictionary = $this->dictionaryService->createDictionary($request->only([
                'name', 'title', 'type', 'description', 'sort', 'status'
            ]));

            return $this->success($dictionary, '字典创建成功');
        } catch (\Exception $e) {
            return $this->error('字典创建失败：' . $e->getMessage());
        }
    }

    /**
     * 显示字典详情
     */
    public function show(Dictionary $dictionary): JsonResponse
    {
        try {
            $dictionary->load(['items' => function ($query) {
                $query->ordered();
            }]);

            return $this->success($dictionary);
        } catch (\Exception $e) {
            return $this->error('获取字典详情失败：' . $e->getMessage());
        }
    }

    /**
     * 显示编辑字典页面
     */
    public function edit(Dictionary $dictionary): View
    {
        return view('lpadmin.dictionary.edit', compact('dictionary'));
    }

    /**
     * 更新字典
     */
    public function update(Request $request, Dictionary $dictionary): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:dictionaries,name,' . $dictionary->id,
            'title' => 'required|string|max:200',
            'type' => 'required|in:select,radio,checkbox',
            'description' => 'nullable|string',
            'sort' => 'nullable|integer|min:0',
            'status' => 'required|in:0,1',
        ], [
            'name.required' => '字典名称不能为空',
            'name.unique' => '字典名称已存在',
            'title.required' => '字典标题不能为空',
            'type.required' => '字典类型不能为空',
            'type.in' => '字典类型无效',
            'status.required' => '状态不能为空',
        ]);

        if ($validator->fails()) {
            return $this->error('验证失败', 422, $validator->errors());
        }

        try {
            $dictionary = $this->dictionaryService->updateDictionary($dictionary, $request->only([
                'name', 'title', 'type', 'description', 'sort', 'status'
            ]));

            return $this->success($dictionary, '字典更新成功');
        } catch (\Exception $e) {
            return $this->error('字典更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除字典
     */
    public function destroy(Dictionary $dictionary): JsonResponse
    {
        try {
            $this->dictionaryService->deleteDictionary($dictionary);

            return $this->success(null, '字典删除成功');
        } catch (\Exception $e) {
            return $this->error('字典删除失败：' . $e->getMessage());
        }
    }

    /**
     * 批量删除字典
     */
    public function batchDestroy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:dictionaries,id',
        ]);

        if ($validator->fails()) {
            return $this->error('参数验证失败', 422, $validator->errors());
        }

        try {
            $ids = $request->ids;

            $count = $this->dictionaryService->batchDeleteDictionaries($ids);

            return $this->success(['count' => $count], "成功删除 {$count} 个字典");
        } catch (\Exception $e) {
            return $this->error('批量删除失败：' . $e->getMessage());
        }
    }

    /**
     * 切换字典状态
     */
    public function toggleStatus(Dictionary $dictionary): JsonResponse
    {
        try {
            $dictionary->status = $dictionary->status === Dictionary::STATUS_ENABLED 
                                ? Dictionary::STATUS_DISABLED 
                                : Dictionary::STATUS_ENABLED;
            $dictionary->save();

            return $this->success([
                'status' => $dictionary->status,
                'status_label' => $dictionary->status_label,
            ], '状态更新成功');
        } catch (\Exception $e) {
            return $this->error('状态更新失败：' . $e->getMessage());
        }
    }

    /**
     * 获取字典统计信息
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->dictionaryService->getStatistics();

            return $this->success($stats);
        } catch (\Exception $e) {
            return $this->error('获取统计信息失败：' . $e->getMessage());
        }
    }

    /**
     * 获取字典数据API（用于前端调用）
     */
    public function getDictData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|exists:dictionaries,name',
            'enabled_only' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('参数验证失败', 422, $validator->errors());
        }

        try {
            $name = $request->input('name');
            $enabledOnly = $request->input('enabled_only', true);

            $data = $this->dictionaryService->getDictData($name, $enabledOnly);

            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error('获取字典数据失败：' . $e->getMessage());
        }
    }

    /**
     * 获取字典选项API（用于表单）
     */
    public function getDictOptions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|exists:dictionaries,name',
            'enabled_only' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('参数验证失败', 422, $validator->errors());
        }

        try {
            $name = $request->input('name');
            $enabledOnly = $request->input('enabled_only', true);

            $options = $this->dictionaryService->getDictOptions($name, $enabledOnly);

            return $this->success($options);
        } catch (\Exception $e) {
            return $this->error('获取字典选项失败：' . $e->getMessage());
        }
    }

    /**
     * 清除字典缓存API
     */
    public function clearCache(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|exists:dictionaries,name',
        ]);

        if ($validator->fails()) {
            return $this->error('参数验证失败', 422, $validator->errors());
        }

        try {
            $name = $request->input('name');
            $this->dictionaryService->clearDictCache($name);

            $message = $name ? "字典 {$name} 缓存清除成功" : '所有字典缓存清除成功';
            return $this->success(null, $message);
        } catch (\Exception $e) {
            return $this->error('清除缓存失败：' . $e->getMessage());
        }
    }

    /**
     * 字典使用示例页面
     */
    public function usage(): View
    {
        try {
            // 获取一些示例字典数据
            $userStatusDict = $this->dictionaryService->getDictData('user_status', true);
            $userTypeDict = $this->dictionaryService->getDictData('user_type', true);
            $userTagsDict = $this->dictionaryService->getDictData('user_tags', true);

            return view('lpadmin.dictionary.usage', compact(
                'userStatusDict',
                'userTypeDict',
                'userTagsDict'
            ));
        } catch (\Exception $e) {
            // 如果字典不存在，传递空数组
            return view('lpadmin.dictionary.usage', [
                'userStatusDict' => [],
                'userTypeDict' => [],
                'userTagsDict' => []
            ]);
        }
    }
}
