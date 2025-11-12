<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>编辑菜单</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

    <form class="layui-form" action="">

        <div class="mainBox">
            <div class="main-container mr-5">

                <div class="layui-form-item">
                    <label class="layui-form-label">父级菜单</label>
                    <div class="layui-input-block">
                        <select name="parent_id" id="parent_id">
                            @foreach($parentOptions as $option)
                                <option value="{{ $option['id'] }}" {{ $menu->parent_id == $option['id'] ? 'selected' : '' }}>
                                    {{ $option['title'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">菜单标题</label>
                    <div class="layui-input-block">
                        <input type="text" name="title" value="{{ $menu->title }}" class="layui-input" placeholder="请输入菜单标题" lay-verify="required">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">菜单标识</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" value="{{ $menu->name }}" class="layui-input" placeholder="请输入菜单标识，如：user.index" lay-verify="required">
                    </div>
                </div>

                @include('lpadmin.components.icon-picker', [
                    'name' => 'icon',
                    'value' => $menu->icon,
                    'label' => '菜单图标',
                    'placeholder' => '请选择图标',
                    'id' => 'menu-icon'
                ])

                {{-- 使用通用单选框组件 - 菜单类型 --}}
                @include('lpadmin.components.radio-group', [
                    'name' => 'type',
                    'label' => '菜单类型',
                    'required' => true,
                    'options' => [
                        ['value' => 'menu', 'title' => '菜单'],
                        ['value' => 'button', 'title' => '按钮'],
                        ['value' => 'api', 'title' => '接口']
                    ],
                    'default' => $menu->type,
                    'help' => $menu->type == 'menu' ? '菜单：显示在左侧菜单栏中的页面链接' :
                             ($menu->type == 'button' ? '按钮：不在菜单中显示，用于权限控制的操作标识（如：添加、编辑、删除等）' :
                             '接口：API接口权限控制')
                ])

                <div class="layui-form-item">
                    <label class="layui-form-label">菜单链接</label>
                    <div class="layui-input-block">
                        <input type="text" name="url" value="{{ $menu->url }}" class="layui-input" placeholder="请输入菜单链接，如：/lpadmin/user">
                    </div>
                </div>



                <div class="layui-form-item">
                    <label class="layui-form-label">打开方式</label>
                    <div class="layui-input-block">
                        <select name="target">
                            <option value="_self" {{ $menu->target == '_self' ? 'selected' : '' }}>当前窗口</option>
                            <option value="_blank" {{ $menu->target == '_blank' ? 'selected' : '' }}>新窗口</option>
                            <option value="_iframe" {{ $menu->target == '_iframe' ? 'selected' : '' }}>框架内</option>
                        </select>
                    </div>
                </div>

                {{-- 使用通用单选框组件 - 是否显示 --}}
                @include('lpadmin.components.radio-group', [
                    'type' => 'show',
                    'name' => 'is_show',
                    'label' => '是否显示',
                    'required' => true,
                    'default' => $menu->is_show
                ])

                {{-- 使用通用单选框组件 - 状态 --}}
                @include('lpadmin.components.radio-group', [
                    'type' => 'status',
                    'name' => 'status',
                    'label' => '状态',
                    'required' => true,
                    'default' => $menu->status
                ])

                <div class="layui-form-item">
                    <label class="layui-form-label required">排序</label>
                    <div class="layui-input-block">
                        <input type="number" name="sort" value="{{ $menu->sort }}" class="layui-input" placeholder="请输入排序值，数值越大越靠前" lay-verify="required">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">备注</label>
                    <div class="layui-input-block">
                        <textarea name="remark" class="layui-textarea" placeholder="请输入备注">{{ $menu->remark }}</textarea>
                    </div>
                </div>

            </div>
        </div>

        <div class="bottom">
            <div class="button-container">
                <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit lay-filter="save">
                    <i class="layui-icon layui-icon-ok"></i>
                    提交
                </button>
                <button type="reset" class="pear-btn pear-btn-md">
                    <i class="layui-icon layui-icon-refresh"></i>
                    重置
                </button>
            </div>
        </div>

    </form>

    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script src="/static/admin/js/radio-fix.js"></script>
    <script>

        // 相关接口
        const SAVE_API = "{{ route('lpadmin.menu.update', $menu->id) }}";

        layui.use(["form"], function () {
            let form = layui.form;
            let $ = layui.$;

            // 初始化单选框
            if (window.RadioHelper) {
                RadioHelper.init('type');
                RadioHelper.init('is_show');
                RadioHelper.init('status');

                // 设置当前值
                RadioHelper.setValue('type', '{{ $menu->type }}');
                RadioHelper.setValue('is_show', '{{ $menu->is_show }}');
                RadioHelper.setValue('status', '{{ $menu->status }}');
            }

            // 表单提交
            form.on('submit(save)', function (data) {
                // 修复单选框数据
                if (window.RadioHelper) {
                    RadioHelper.fixFormData(data.field, ['type', 'is_show', 'status']);
                }
                $.ajax({
                    url: SAVE_API,
                    method: 'PUT',
                    data: data.field,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.code === 0) {
                            layer.msg(res.message, {icon: 1});
                            // 刷新父页面表格
                            if (parent.refreshTable && typeof parent.refreshTable === 'function') {
                                parent.refreshTable();
                            }
                            // 关闭弹窗
                            if (parent.layer) {
                                let index = parent.layer.getFrameIndex(window.name);
                                parent.layer.close(index);
                            }
                        } else {
                            layer.msg(res.message, {icon: 2});
                        }
                    },
                    error: function (xhr) {
                        let errorMsg = '提交失败';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        layer.msg(errorMsg, {icon: 2});
                    }
                });
                return false;
            });

            // 菜单类型变化事件
            if (window.RadioHelper) {
                RadioHelper.onChange('type', function(value, element, data) {
                    let description = '';

                    switch(value) {
                        case 'menu':
                            description = '菜单：显示在左侧菜单栏中的页面链接';
                            break;
                        case 'button':
                            description = '按钮：不在菜单中显示，用于权限控制的操作标识（如：添加、编辑、删除等）';
                            break;
                        case 'api':
                            description = '接口：API接口权限控制';
                            break;
                    }

                    // 更新帮助文本
                    let $helpText = $('input[name="type"]').closest('.layui-form-item').find('.layui-word-aux');
                    if ($helpText.length > 0) {
                        $helpText.text(description);
                    }
                });
            }

        });

    </script>

</body>
</html>
