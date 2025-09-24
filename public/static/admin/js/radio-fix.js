/**
 * Layui单选框统一处理工具 - 简化版
 * 解决layui渲染后单选框value值错乱的问题
 * 使用data-value属性存储真实值，简化处理逻辑
 */
window.RadioHelper = {

    /**
     * 初始化单选框（简化版）
     * @param {string} name - 单选框的name属性
     */
    init: function(name) {
        try {
            layui.$('input[name="' + name + '"][data-value]').each(function() {
                var $radio = layui.$(this);
                var dataValue = $radio.attr('data-value');

                if (dataValue) {
                    // 确保value属性与data-value一致
                    $radio.attr('value', dataValue);

                    // 如果没有设置lay-filter，自动设置
                    if (!$radio.attr('lay-filter')) {
                        $radio.attr('lay-filter', name);
                    }
                }
            });

            // 重新渲染表单
            if (layui.form) {
                layui.form.render('radio');
            }


        } catch (e) {
            console.error('RadioHelper: 初始化失败', e);
        }
    },

    /**
     * 设置单选框值（简化版）
     * @param {string} name - 单选框的name属性
     * @param {string|number} value - 要设置的值
     */
    setValue: function(name, value) {
        var self = this;
        setTimeout(function() {
            // 取消所有选中
            layui.$('input[name="' + name + '"]').prop('checked', false);

            // 根据data-value属性选中对应的单选框
            var $target = layui.$('input[name="' + name + '"][data-value="' + value + '"]');
            if ($target.length > 0) {
                $target.prop('checked', true);
            }

            // 重新渲染
            layui.form.render('radio');
        }, 100);
    },

    /**
     * 获取单选框的真实值（简化版）
     * @param {string} name - 单选框的name属性
     * @returns {string|null} 选中的值
     */
    getValue: function(name) {
        var $checked = layui.$('input[name="' + name + '"]:checked');
        if ($checked.length > 0) {
            var value = $checked.attr('data-value') || $checked.val();

            return value;
        }
        return null;
    },

    /**
     * 监听单选框变化（简化版）
     * @param {string} filterName - lay-filter属性值
     * @param {function} callback - 回调函数，参数为(value, element, data)
     */
    onChange: function(filterName, callback) {
        layui.form.on('radio(' + filterName + ')', function(data) {
            var $elem = layui.$(data.elem);
            var realValue = $elem.attr('data-value') || $elem.val();

            if (callback && typeof callback === 'function') {
                callback(realValue, data.elem, data);
            }
        });
    },

    /**
     * 表单提交时修复单选框数据（简化版）
     * @param {object} formData - 表单数据对象
     * @param {array} radioNames - 需要修复的单选框name数组，如['status']
     */
    fixFormData: function(formData, radioNames) {
        var self = this;

        // 如果没有指定字段，默认处理status字段
        if (!radioNames || radioNames.length === 0) {
            radioNames = ['status'];
        }

        try {
            for (var i = 0; i < radioNames.length; i++) {
                var fieldName = radioNames[i];
                var realValue = self.getValue(fieldName);
                if (realValue !== null) {
                    formData[fieldName] = realValue;

                }
            }
        } catch (e) {
            console.error('RadioHelper: fixFormData 出错', e);
        }

        return formData;
    },


};

// 常用的状态映射（保持向后兼容）
window.RadioHelper.STATUS_MAP = {"启用": "1", "禁用": "0"};
window.RadioHelper.SHOW_MAP = {"显示": "1", "隐藏": "0"};
window.RadioHelper.TYPE_MAP = {"菜单": "1", "按钮": "2"};
window.RadioHelper.GENDER_MAP = {"男": "1", "女": "0"};

// 移除自动初始化，避免循环调用问题
// 需要手动调用 RadioHelper.autoInit() 来初始化
