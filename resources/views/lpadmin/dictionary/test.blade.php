@extends('lpadmin.layouts.app')

@section('title', '数据字典测试')

@section('content')
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">
                    <span class="layui-icon layui-icon-test"></span>
                    数据字典功能测试
                </div>
                <div class="layui-card-body">
                    <div class="layui-row layui-col-space15">
                        <!-- 用户状态测试 -->
                        <div class="layui-col-md6">
                            <div class="layui-card">
                                <div class="layui-card-header">用户状态字典测试</div>
                                <div class="layui-card-body">
                                    <h4>原始数据：</h4>
                                    <pre>{{ json_encode(dict_data('user_status'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    
                                    <h4>选项数据：</h4>
                                    <pre>{{ json_encode(dict_options('user_status'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    
                                    <h4>标签获取：</h4>
                                    <p>active: {{ dict_label('user_status', 'active') }}</p>
                                    <p>disabled: {{ dict_label('user_status', 'disabled') }}</p>
                                    
                                    <h4>颜色获取：</h4>
                                    <p>active: {{ dict_color('user_status', 'active') }}</p>
                                    <p>disabled: {{ dict_color('user_status', 'disabled') }}</p>
                                    
                                    <h4>标签HTML：</h4>
                                    <p>active: {!! dict_badge_html('user_status', 'active') !!}</p>
                                    <p>disabled: {!! dict_badge_html('user_status', 'disabled') !!}</p>
                                    <p>pending: {!! dict_badge_html('user_status', 'pending') !!}</p>
                                    <p>deleted: {!! dict_badge_html('user_status', 'deleted') !!}</p>
                                </div>
                            </div>
                        </div>

                        <!-- 性别测试 -->
                        <div class="layui-col-md6">
                            <div class="layui-card">
                                <div class="layui-card-header">性别字典测试</div>
                                <div class="layui-card-body">
                                    <h4>下拉选择HTML：</h4>
                                    <select class="layui-input">
                                        {!! dict_select_html('gender', 'male') !!}
                                    </select>
                                    
                                    <h4>单选框HTML：</h4>
                                    <div class="layui-form">
                                        {!! dict_radio_html('gender', 'gender', 'female') !!}
                                    </div>
                                    
                                    <h4>JavaScript数组：</h4>
                                    <pre>{{ dict_js_array('gender') }}</pre>
                                    
                                    <h4>JavaScript选项：</h4>
                                    <pre>{{ dict_js_options('gender') }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="layui-row layui-col-space15" style="margin-top: 15px;">
                        <!-- 用户标签测试 -->
                        <div class="layui-col-md6">
                            <div class="layui-card">
                                <div class="layui-card-header">用户标签字典测试</div>
                                <div class="layui-card-body">
                                    <h4>复选框HTML：</h4>
                                    <div class="layui-form">
                                        {!! dict_checkbox_html('user_tags', 'tags', ['vip', 'active']) !!}
                                    </div>
                                    
                                    <h4>所有标签：</h4>
                                    @foreach(dict_data('user_tags') as $tag)
                                        {!! dict_badge_html('user_tags', $tag['value']) !!}
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- 订单状态测试 -->
                        <div class="layui-col-md6">
                            <div class="layui-card">
                                <div class="layui-card-header">订单状态字典测试</div>
                                <div class="layui-card-body">
                                    <h4>状态流程：</h4>
                                    @foreach(dict_data('order_status') as $status)
                                        {!! dict_badge_html('order_status', $status['value']) !!}
                                        @if(!$loop->last) → @endif
                                    @endforeach
                                    
                                    <h4>状态说明：</h4>
                                    <ul>
                                        @foreach(dict_data('order_status') as $status)
                                            <li>
                                                {!! dict_badge_html('order_status', $status['value']) !!}
                                                {{ $status['description'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="layui-row layui-col-space15" style="margin-top: 15px;">
                        <!-- 优先级测试 -->
                        <div class="layui-col-md12">
                            <div class="layui-card">
                                <div class="layui-card-header">优先级字典测试</div>
                                <div class="layui-card-body">
                                    <h4>优先级列表：</h4>
                                    <div class="layui-row">
                                        @foreach(dict_data('priority_level') as $priority)
                                            <div class="layui-col-md3" style="text-align: center; margin-bottom: 10px;">
                                                {!! dict_badge_html('priority_level', $priority['value']) !!}
                                                <br>
                                                <small>{{ $priority['description'] }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 表单测试 -->
                    <div class="layui-row layui-col-space15" style="margin-top: 15px;">
                        <div class="layui-col-md12">
                            <div class="layui-card">
                                <div class="layui-card-header">表单集成测试</div>
                                <div class="layui-card-body">
                                    <form class="layui-form" lay-filter="testForm">
                                        <div class="layui-row layui-col-space15">
                                            <div class="layui-col-md4">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">用户状态</label>
                                                    <div class="layui-input-block">
                                                        <select name="status">
                                                            {!! dict_select_html('user_status', 'active') !!}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="layui-col-md4">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">性别</label>
                                                    <div class="layui-input-block">
                                                        {!! dict_radio_html('gender', 'gender', 'male') !!}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="layui-col-md4">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">优先级</label>
                                                    <div class="layui-input-block">
                                                        <select name="priority">
                                                            {!! dict_select_html('priority_level', 'high') !!}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">用户标签</label>
                                            <div class="layui-input-block">
                                                {!! dict_checkbox_html('user_tags', 'tags', ['vip', 'verified']) !!}
                                            </div>
                                        </div>
                                        
                                        <div class="layui-form-item">
                                            <div class="layui-input-block">
                                                <button class="layui-btn" lay-submit lay-filter="submit">提交测试</button>
                                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
layui.use(['form', 'layer'], function(){
    var form = layui.form;
    var layer = layui.layer;

    // 表单提交测试
    form.on('submit(submit)', function(data){
        layer.alert('表单数据：<pre>' + JSON.stringify(data.field, null, 2) + '</pre>', {
            title: '提交测试结果',
            area: ['500px', '400px']
        });
        return false;
    });
});
</script>
@endpush
