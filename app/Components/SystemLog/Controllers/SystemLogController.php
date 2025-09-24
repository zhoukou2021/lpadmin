<?php

namespace App\Components\SystemLog\Controllers;

use App\Http\Controllers\LPadmin\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\LPadmin\AdminLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

/**
 * 系统日志控制器
 */
class SystemLogController extends BaseController
{
    /**
     * 显示日志列表
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->expectsJson()) {
            $query = AdminLog::with('admin:id,username,nickname');

            // 搜索条件
            if ($request->filled('admin_id')) {
                $query->where('admin_id', $request->admin_id);
            }

            if ($request->filled('action')) {
                $query->where('action', 'like', '%' . $request->action . '%');
            }

            if ($request->filled('admin_username')) {
                $query->where('admin_username', 'like', '%' . $request->admin_username . '%');
            }

            if ($request->filled('ip')) {
                $query->where('ip', 'like', '%' . $request->ip . '%');
            }

            // 时间范围搜索
            if ($request->filled('created_at') && is_array($request->created_at)) {
                $dates = $request->created_at;
                if (!empty($dates[0])) {
                    $query->where('created_at', '>=', $dates[0] . ' 00:00:00');
                }
                if (!empty($dates[1])) {
                    $query->where('created_at', '<=', $dates[1] . ' 23:59:59');
                }
            }

            // 排序
            $field = $request->get('field', 'id');
            $order = $request->get('order', 'desc');
            $query->orderBy($field, $order);

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 15);

            $logs = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $data = [];
            foreach ($logs->items() as $log) {
                $item = [
                    'id' => $log->id,
                    'admin_id' => $log->admin_id,
                    'admin_name' => $log->admin ? $this->cleanUtf8($log->admin->username) : $this->cleanUtf8($log->admin_username ?? '系统'),
                    'admin_nickname' => $log->admin ? $this->cleanUtf8($log->admin->nickname) : $this->cleanUtf8($log->admin_username ?? '系统'),
                    'action' => $this->cleanUtf8($log->action),
                    'description' => $this->cleanUtf8($log->route_name ?? ''),
                    'ip' => $log->ip,
                    'user_agent' => $this->cleanUtf8($log->user_agent ?? ''),
                    'data' => $log->request_data,
                    'created_at' => $log->created_at ?? '',
                ];
                $data[] = $item;
            }

            return response()->json([
                'code' => 0,
                'msg' => '',
                'count' => $logs->total(),
                'data' => $data,
            ]);
        }

        return view('SystemLog::index');
    }

    /**
     * 显示日志详情
     */
    public function show(AdminLog $log): JsonResponse
    {
        $log->load('admin:id,username,nickname');
        
        $data = [
            'id' => $log->id,
            'admin_id' => $log->admin_id,
            'admin_name' => $log->admin ? $log->admin->username : '系统',
            'admin_nickname' => $log->admin ? $log->admin->nickname : '系统',
            'action' => $log->action,
            'description' => $log->description,
            'ip' => $log->ip,
            'user_agent' => $log->user_agent,
            'data' => $log->data,
            'created_at' => $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '',
        ];
        
        return $this->success($data);
    }

    /**
     * 删除日志
     */
    public function destroy(AdminLog $log): JsonResponse
    {
        try {
            $log->delete();
            
            $this->log('delete', '删除系统日志', ['log_id' => $log->id]);
            
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量删除日志
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return $this->error('请选择要删除的日志');
        }

        try {
            $count = AdminLog::whereIn('id', $ids)->delete();
            
            $this->log('batch_delete', '批量删除系统日志', [
                'count' => $count,
                'ids' => $ids
            ]);
            
            return $this->success(null, "成功删除 {$count} 条日志");
        } catch (\Exception $e) {
            return $this->error('批量删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 清空日志
     */
    public function clear(Request $request): JsonResponse
    {
        $days = $request->input('days', 0);
        
        try {
            if ($days > 0) {
                // 删除指定天数之前的日志
                $date = now()->subDays($days);
                $count = AdminLog::where('created_at', '<', $date)->delete();
                $message = "成功清空 {$days} 天前的日志，共 {$count} 条";
            } else {
                // 清空所有日志
                $count = AdminLog::count();
                AdminLog::truncate();
                $message = "成功清空所有日志，共 {$count} 条";
            }
            
            $this->log('clear', '清空系统日志', [
                'days' => $days,
                'count' => $count
            ]);
            
            return $this->success(null, $message);
        } catch (\Exception $e) {
            return $this->error('清空日志失败: ' . $e->getMessage());
        }
    }

    /**
     * 导出日志
     */
    public function export(Request $request)
    {
        try {
            $query = AdminLog::with('admin:id,username,nickname');

            // 应用搜索条件
            if ($request->filled('admin_id')) {
                $query->where('admin_id', $request->admin_id);
            }

            if ($request->filled('action')) {
                $query->where('action', 'like', '%' . $request->action . '%');
            }

            if ($request->filled('created_at') && is_array($request->created_at)) {
                $dates = $request->created_at;
                if (!empty($dates[0])) {
                    $query->where('created_at', '>=', $dates[0] . ' 00:00:00');
                }
                if (!empty($dates[1])) {
                    $query->where('created_at', '<=', $dates[1] . ' 23:59:59');
                }
            }

            // 限制导出数量
            $limit = min($request->get('limit', 1000), 10000);
            $logs = $query->orderBy('id', 'desc')->limit($limit)->get();

            // 生成CSV内容
            $csvData = "ID,管理员,操作,描述,IP地址,时间\n";
            foreach ($logs as $log) {
                $adminName = $log->admin ? $log->admin->username : '系统';
                $csvData .= sprintf(
                    "%d,%s,%s,%s,%s,%s\n",
                    $log->id,
                    $adminName,
                    $log->action,
                    str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $log->description),
                    $log->ip,
                    $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : ''
                );
            }

            $filename = '系统日志_' . date('Y-m-d_H-i-s') . '.csv';
            
            $this->log('export', '导出系统日志', [
                'count' => count($logs),
                'filename' => $filename
            ]);

            return Response::make($csvData, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            return $this->error('导出失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取统计信息
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_logs' => AdminLog::count(),
                'today_logs' => AdminLog::whereDate('created_at', today())->count(),
                'week_logs' => AdminLog::where('created_at', '>=', now()->subWeek())->count(),
                'month_logs' => AdminLog::where('created_at', '>=', now()->subMonth())->count(),
                'top_actions' => AdminLog::select('action', DB::raw('count(*) as count'))
                    ->groupBy('action')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
                'top_admins' => AdminLog::select('admin_id', DB::raw('count(*) as count'))
                    ->with('admin:id,username,nickname')
                    ->groupBy('admin_id')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'admin_name' => $item->admin ? $item->admin->username : '系统',
                            'count' => $item->count
                        ];
                    }),
            ];

            return $this->success($stats);
        } catch (\Exception $e) {
            return $this->error('获取统计信息失败: ' . $e->getMessage());
        }
    }
}
