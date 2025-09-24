/**
 * LPadmin 管理后台 JavaScript
 */

layui.define(['layer', 'table', 'form', 'element'], function(exports) {
    var $ = layui.$,
        layer = layui.layer,
        table = layui.table,
        form = layui.form,
        element = layui.element;

    var admin = {
        // 配置
        config: {
            base_url: '',
            token: $('meta[name="csrf-token"]').attr('content')
        },

        // 初始化
        init: function() {
            this.bindEvents();
            this.setupAjax();
        },

        // 绑定事件
        bindEvents: function() {
            // 退出登录
            $(document).on('click', '[data-action="logout"]', function() {
                admin.logout();
            });

            // 刷新页面
            $(document).on('click', '[data-action="refresh"]', function() {
                location.reload();
            });

            // 全屏切换
            $(document).on('click', '[data-action="fullscreen"]', function() {
                admin.toggleFullscreen();
            });
        },

        // 设置Ajax默认配置
        setupAjax: function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': this.config.token
                },
                beforeSend: function() {
                    // 显示加载动画
                },
                complete: function() {
                    // 隐藏加载动画
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 401) {
                        layer.msg('登录已过期，请重新登录', {icon: 2}, function() {
                            location.href = '/lpadmin/login';
                        });
                    } else if (xhr.status === 403) {
                        layer.msg('没有权限执行此操作', {icon: 2});
                    } else {
                        layer.msg('请求失败：' + error, {icon: 2});
                    }
                }
            });
        },

        // 退出登录
        logout: function() {
            layer.confirm('确定要退出登录吗？', {icon: 3, title: '提示'}, function(index) {
                $.post('/lpadmin/logout', function(res) {
                    if (res.code === 200) {
                        layer.msg('退出成功', {icon: 1}, function() {
                            location.href = '/lpadmin/login';
                        });
                    } else {
                        layer.msg(res.message || '退出失败', {icon: 2});
                    }
                });
                layer.close(index);
            });
        },

        // 全屏切换
        toggleFullscreen: function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        },

        // 通用表格配置
        tableConfig: {
            page: true,
            limit: 20,
            limits: [10, 20, 50, 100],
            loading: true,
            text: {
                none: '暂无相关数据'
            }
        },

        // 渲染表格
        renderTable: function(options) {
            return table.render($.extend({}, this.tableConfig, options));
        },

        // 通用删除确认
        confirmDelete: function(url, callback) {
            layer.confirm('确定要删除吗？删除后不可恢复！', {
                icon: 3,
                title: '删除确认'
            }, function(index) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function(res) {
                        if (res.code === 200) {
                            layer.msg('删除成功', {icon: 1});
                            if (typeof callback === 'function') {
                                callback();
                            }
                        } else {
                            layer.msg(res.message || '删除失败', {icon: 2});
                        }
                    }
                });
                layer.close(index);
            });
        },

        // 批量删除确认
        confirmBatchDelete: function(url, ids, callback) {
            if (!ids || ids.length === 0) {
                layer.msg('请选择要删除的数据', {icon: 2});
                return;
            }

            layer.confirm('确定要删除选中的 ' + ids.length + ' 条数据吗？', {
                icon: 3,
                title: '批量删除确认'
            }, function(index) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {ids: ids},
                    success: function(res) {
                        if (res.code === 200) {
                            layer.msg('删除成功', {icon: 1});
                            if (typeof callback === 'function') {
                                callback();
                            }
                        } else {
                            layer.msg(res.message || '删除失败', {icon: 2});
                        }
                    }
                });
                layer.close(index);
            });
        },

        // 状态切换
        toggleStatus: function(url, callback) {
            $.ajax({
                url: url,
                type: 'POST',
                success: function(res) {
                    if (res.code === 200) {
                        layer.msg('操作成功', {icon: 1});
                        if (typeof callback === 'function') {
                            callback();
                        }
                    } else {
                        layer.msg(res.message || '操作失败', {icon: 2});
                    }
                }
            });
        },

        // 打开弹窗
        openDialog: function(title, url, options) {
            options = options || {};
            return layer.open($.extend({
                type: 2,
                title: title,
                content: url,
                area: ['80%', '80%'],
                maxmin: true,
                shadeClose: false,
                shade: 0.3
            }, options));
        },

        // 关闭弹窗并刷新父页面
        closeDialog: function(msg) {
            var index = parent.layer.getFrameIndex(window.name);
            if (msg) {
                parent.layer.msg(msg, {icon: 1});
            }
            parent.layer.close(index);
            parent.location.reload();
        },

        // 格式化日期
        formatDate: function(timestamp, format) {
            if (!timestamp) return '-';
            var date = new Date(timestamp);
            format = format || 'yyyy-MM-dd hh:mm:ss';
            
            var o = {
                'M+': date.getMonth() + 1,
                'd+': date.getDate(),
                'h+': date.getHours(),
                'm+': date.getMinutes(),
                's+': date.getSeconds(),
                'q+': Math.floor((date.getMonth() + 3) / 3),
                'S': date.getMilliseconds()
            };
            
            if (/(y+)/.test(format)) {
                format = format.replace(RegExp.$1, (date.getFullYear() + '').substr(4 - RegExp.$1.length));
            }
            
            for (var k in o) {
                if (new RegExp('(' + k + ')').test(format)) {
                    format = format.replace(RegExp.$1, RegExp.$1.length === 1 ? o[k] : ('00' + o[k]).substr(('' + o[k]).length));
                }
            }
            
            return format;
        },

        // 格式化文件大小
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 B';
            var k = 1024;
            var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
        }
    };

    // 初始化
    admin.init();

    // 导出模块
    exports('admin', admin);
});
