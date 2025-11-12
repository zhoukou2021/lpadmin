<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use App\Models\LPadmin\Option;
use App\Helpers\ConfigHelper;

class ConfigController extends BaseController
{
    /**
     * 系统配置页面
     */
    public function index(): View
    {
        return view('lpadmin.config.index');
    }

    /**
     * 获取配置列表数据（AJAX）
     */
    public function select(Request $request): JsonResponse
    {
        $query = Option::query();

        // 搜索条件
        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 排序
        $query->orderBy('group')->orderBy('sort')->orderBy('id');

        // 分页
        $options = $query->paginate($request->get('limit', 20));
        return $this->paginate($options);
    }

    /**
     * 获取配置分组
     */
    public function groups(): JsonResponse
    {
        $groups = Option::select('group')
            ->selectRaw('COUNT(*) as config_count')
            ->selectRaw('MAX(created_at) as created_at')
            ->groupBy('group')
            ->orderBy('group')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->group,
                    'title' => $this->getGroupTitle($item->group),
                    'description' => $this->getGroupDescription($item->group),
                    'config_count' => $item->config_count,
                    'created_at' => $item->created_at,
                ];
            });

        return $this->success($groups);
    }

    /**
     * 获取分组标题
     */
    private function getGroupTitle($group): string
    {
        // 先从数据库查找分组元数据
        $metaName = $group . '_group_meta';
        $metaOption = Option::where('group', $group)
            ->where('name', $metaName)
            ->first();
        
        if ($metaOption && $metaOption->title) {
            return $metaOption->title;
        }

        // 如果数据库中没有，使用默认映射
        $titles = [
            'system' => '系统配置',
            'security' => '安全配置',
            'upload' => '上传配置',
            'mail' => '邮件配置',
            'cache' => '缓存配置',
            'deepseek' => 'DeepSeek AI配置',
        ];

        return $titles[$group] ?? ucfirst($group) . '配置';
    }

    /**
     * 获取分组描述
     */
    private function getGroupDescription($group): string
    {
        // 先从数据库查找分组元数据
        $metaName = $group . '_group_meta';
        $metaOption = Option::where('group', $group)
            ->where('name', $metaName)
            ->first();
        
        if ($metaOption && $metaOption->description) {
            return $metaOption->description;
        }

        // 如果数据库中没有，使用默认映射
        $descriptions = [
            'system' => '网站基本信息配置',
            'security' => '系统安全相关配置',
            'upload' => '文件上传相关配置',
            'mail' => '邮件发送相关配置',
            'cache' => '缓存系统相关配置',
            'deepseek' => 'DeepSeek AI相关配置',
        ];

        return $descriptions[$group] ?? $group . '分组配置';
    }

    /**
     * 配置分组管理页面
     */
    public function groupsPage(): View
    {
        return view('lpadmin.config.groups');
    }

    /**
     * 创建配置分组
     */
    public function createGroup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:options,group',
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => '分组名称不能为空',
            'name.unique' => '分组名称已存在',
            'title.required' => '分组标题不能为空',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            // 创建分组元数据配置项
            $metaName = $request->name . '_group_meta';
            Option::create([
                'group' => $request->name,
                'name' => $metaName,
                'title' => $request->title,
                'value' => json_encode([
                    'title' => $request->title,
                    'description' => $request->description ?? '',
                ]),
                'type' => 'text',
                'description' => $request->description ?? '',
                'sort' => 0,
            ]);

            // 清除分组缓存
            Option::clearCache();

            $this->log('create', '创建配置分组', [
                'group' => $request->name,
                'title' => $request->title
            ]);

            return $this->success(null, '分组创建成功');
        } catch (\Exception $e) {
            return $this->error('分组创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 更新配置分组
     */
    public function updateGroup(Request $request, string $group): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ], [
            'title.required' => '分组标题不能为空',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            // 查找或创建分组元数据配置项
            // 使用特殊名称存储分组元数据：{group}_group_meta
            $metaName = $group . '_group_meta';
            $metaOption = Option::where('group', $group)
                ->where('name', $metaName)
                ->first();

            if ($metaOption) {
                // 更新现有元数据
                $metaOption->update([
                    'title' => $request->title,
                    'description' => $request->description ?? '',
                ]);
            } else {
                // 创建新的元数据配置项
                // 获取该分组的最小sort值，将元数据放在最前面
                $minSort = Option::where('group', $group)->min('sort') ?? 0;
                
                Option::create([
                    'group' => $group,
                    'name' => $metaName,
                    'title' => $request->title,
                    'value' => json_encode([
                        'title' => $request->title,
                        'description' => $request->description ?? '',
                    ]),
                    'type' => 'text',
                    'description' => $request->description ?? '',
                    'sort' => $minSort - 1, // 放在最前面
                ]);
            }

            // 清除分组缓存
            Option::clearCache();

            $this->log('update', '更新配置分组', [
                'group' => $group,
                'title' => $request->title
            ]);

            return $this->success(null, '分组更新成功');
        } catch (\Exception $e) {
            return $this->error('分组更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除配置分组
     */
    public function deleteGroup(string $group): JsonResponse
    {
        try {
            $count = Option::where('group', $group)->count();

            if ($count > 0) {
                return $this->error('该分组下还有 ' . $count . ' 个配置项，无法删除');
            }

            $this->log('delete', '删除配置分组', ['group' => $group]);

            return $this->success(null, '分组删除成功');
        } catch (\Exception $e) {
            return $this->error('分组删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量删除配置分组
     */
    public function batchDeleteGroups(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'names' => 'required|array',
            'names.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $deleted = 0;
            $errors = [];

            foreach ($request->names as $group) {
                $count = Option::where('group', $group)->count();

                if ($count > 0) {
                    $errors[] = "分组 {$group} 下还有 {$count} 个配置项，无法删除";
                } else {
                    $deleted++;
                }
            }

            $this->log('batchDelete', '批量删除配置分组', [
                'groups' => $request->names,
                'deleted' => $deleted,
                'errors' => count($errors)
            ]);

            if (!empty($errors)) {
                return $this->error('部分分组删除失败：' . implode('；', $errors));
            }

            return $this->success(null, "成功删除 {$deleted} 个分组");
        } catch (\Exception $e) {
            return $this->error('批量删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取指定分组的配置
     */
    public function group(string $group): JsonResponse
    {
        $options = Option::where('group', $group)
                        ->orderBy('sort')
                        ->get();

        return $this->success($options);
    }

    /**
     * 创建配置页面
     */
    public function create(): View
    {
        $groups = Option::select('group')->groupBy('group')->pluck('group');
        return view('lpadmin.config.create', compact('groups'));
    }

    /**
     * 保存配置
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'group' => 'required|string|max:50',
            'name' => 'required|string|max:100|unique:options,name',
            'title' => 'required|string|max:100',
            'value' => 'nullable|string',
            'type' => 'required|in:text,textarea,number,select,radio,checkbox,switch,image,file,date,datetime,color,richtext',
            'options' => 'nullable|string',
            'description' => 'nullable|string',
            'sort' => 'nullable|integer|min:0',
            'is_i18n' => 'nullable|boolean',
        ], [
            'group.required' => '配置分组不能为空',
            'name.required' => '配置名称不能为空',
            'name.unique' => '配置名称已存在',
            'title.required' => '配置标题不能为空',
            'type.required' => '配置类型不能为空',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $data = $request->only([
                'group', 'name', 'title', 'value', 'type',
                'options', 'description', 'sort', 'is_i18n'
            ]);

            $data['sort'] = $data['sort'] ?: 0;
            $data['is_i18n'] = $data['is_i18n'] ?? false;

            $option = Option::create($data);

            $this->log('create', '创建系统配置', ['option_id' => $option->id]);

            return $this->success($option, '创建成功');
        } catch (\Exception $e) {
            return $this->error('创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示配置详情
     */
    public function show($id): JsonResponse
    {
        try {
            $option = Option::findOrFail($id);
            return $this->success($option);
        } catch (\Exception $e) {
            return $this->error('配置不存在');
        }
    }

    /**
     * 编辑配置页面
     */
    public function edit($id): View
    {
        $option = Option::findOrFail($id);
        $groups = Option::select('group')->groupBy('group')->pluck('group');
        return view('lpadmin.config.edit', compact('option', 'groups'));
    }

    /**
     * 更新配置
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $option = Option::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'group' => 'required|string|max:50',
                'name' => 'required|string|max:100|unique:options,name,' . $option->id,
                'title' => 'required|string|max:100',
                'value' => 'nullable|string',
                'type' => 'required|in:text,textarea,number,select,radio,checkbox,switch,image,file,date,datetime,color,richtext',
                'options' => 'nullable|string',
                'description' => 'nullable|string',
                'sort' => 'nullable|integer|min:0',
                'is_i18n' => 'nullable|boolean',
            ], [
                'group.required' => '配置分组不能为空',
                'name.required' => '配置名称不能为空',
                'name.unique' => '配置名称已存在',
                'title.required' => '配置标题不能为空',
                'type.required' => '配置类型不能为空',
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors()->first());
            }

            $data = $request->only([
                'group', 'name', 'title', 'value', 'type',
                'options', 'description', 'sort', 'is_i18n'
            ]);

            $data['sort'] = $data['sort'] ?: 0;
            $data['is_i18n'] = $data['is_i18n'] ?? false;

            $option->update($data);

            // 清除缓存
            Option::clearCache($option->name);

            $this->log('update', '更新系统配置', ['option_id' => $option->id]);

            return $this->success($option, '更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除配置
     */
    public function destroy($id): JsonResponse
    {
        try {
            $option = Option::findOrFail($id);

            // 清除缓存
            Option::clearCache($option->name);

            $option->delete();

            $this->log('delete', '删除系统配置', ['option_id' => $option->id]);

            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量删除配置
     */
    public function batchDestroy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:options,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $ids = $request->ids;
            $options = Option::whereIn('id', $ids)->get();

            $deleted = 0;
            foreach ($options as $option) {
                // 清除缓存
                Option::clearCache($option->name);
                $option->delete();
                $deleted++;
            }

            $this->log('delete', '批量删除系统配置', ['count' => $deleted, 'ids' => $ids]);

            return $this->success(null, "成功删除 {$deleted} 个配置");
        } catch (\Exception $e) {
            return $this->error('批量删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量更新配置
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'configs' => 'required|array',
            'configs.*.name' => 'required|string',
            'configs.*.value' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $configs = $request->configs;
            $updated = 0;

            foreach ($configs as $config) {
                $option = Option::where('name', $config['name'])->first();
                if ($option) {
                    $option->update(['value' => $config['value'] ?? '']);
                    Option::clearCache($option->name);
                    $updated++;
                }
            }

            $this->log('update', '批量更新系统配置', ['count' => $updated]);

            return $this->success(null, "成功更新 {$updated} 个配置");
        } catch (\Exception $e) {
            return $this->error('批量更新失败: ' . $e->getMessage());
        }
    }



    /**
     * 系统设置页面
     */
    public function system(): View
    {
        // 加载所有分组配置，按分组与排序展示为 Tabs
        $systemConfigs = Option::orderBy('group')->orderBy('sort')->get();
        return view('lpadmin.config.system', compact('systemConfigs'));
    }

    /**
     * 保存系统设置
     */
    public function saveSystem(Request $request): JsonResponse
    {
        try {
            $configs = $request->except(['_token', '_method']);
            $updated = 0;

            foreach ($configs as $name => $value) {
                $option = Option::where('name', $name)->first();
                if ($option) {
                    // 如果是多语言配置且是 JSON 字符串，直接保存（前端已转换为 JSON）
                    if ($option->is_i18n && in_array($option->type, ['text', 'textarea', 'richtext']) && is_string($value) && $this->isJson($value)) {
                        $option->update(['value' => $value]);
                    } else {
                        $option->update(['value' => $value ?? '']);
                    }
                    Option::clearCache($option->name);
                    $updated++;
                }
            }

            $this->log('update', '保存系统设置', ['count' => $updated]);

            return $this->success(null, '系统设置保存成功');
        } catch (\Exception $e) {
            return $this->error('保存失败: ' . $e->getMessage());
        }
    }

    /**
     * 判断字符串是否为 JSON 格式
     */
    private function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 导出配置
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $group = $request->filled('group') ? $request->group : null;
            $exportData = ConfigHelper::export($group);

            $this->log('export', '导出系统配置', [
                'group' => $group,
                'count' => $exportData['total_count']
            ]);

            return $this->success($exportData, '导出成功');
        } catch (\Exception $e) {
            return $this->error('导出失败: ' . $e->getMessage());
        }
    }

    /**
     * 导入页面
     */
    public function importPage(): View
    {
        return view('lpadmin.config.import');
    }

    /**
     * 导入配置
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:json|max:2048',
            'mode' => 'required|in:merge,replace',
        ], [
            'file.required' => '请选择导入文件',
            'file.mimes' => '文件格式必须为JSON',
            'mode.required' => '请选择导入模式',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $file = $request->file('file');
            $content = file_get_contents($file->getPathname());
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('JSON文件格式错误');
            }

            $mode = $request->mode;
            $result = ConfigHelper::import($data, $mode);

            $this->log('import', '导入系统配置', [
                'mode' => $mode,
                'imported' => $result['imported'],
                'updated' => $result['updated'],
                'skipped' => $result['skipped'],
                'errors_count' => count($result['errors'])
            ]);

            $message = "导入完成：新增 {$result['imported']} 个，更新 {$result['updated']} 个，跳过 {$result['skipped']} 个";

            if (!empty($result['errors'])) {
                $message .= "，错误 " . count($result['errors']) . " 个";
            }

            return $this->success($result, $message);
        } catch (\Exception $e) {
            return $this->error('导入失败: ' . $e->getMessage());
        }
    }
}
