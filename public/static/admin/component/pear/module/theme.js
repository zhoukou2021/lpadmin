layui.define(["jquery","layer"], function (exports) {
	var MOD_NAME = 'theme',
	    $ = layui.jquery;

	var theme = {};
	theme.autoHead = false;

	theme.changeTheme = function (target, autoHead) {
		this.autoHead = autoHead;
		var color = localStorage.getItem("theme-color-color");
		var second = localStorage.getItem("theme-color-second");
		this.colorSet(color, second);
		if (target.frames.length == 0) return;
		for (var i = 0; i < target.frames.length; i++) {
			try {
				// 检查是否可以访问iframe内容（避免跨域错误）
				var frame = target.frames[i];

				// 先尝试访问frame的基本属性来检测跨域
				var canAccess = false;
				try {
					canAccess = frame && frame.location && frame.location.href;
				} catch (e) {
					// 跨域访问被阻止，跳过这个frame
					continue;
				}

				if (!canAccess || !frame.layui) continue;

				// 检查是否有theme模块
				if (frame.layui.theme && typeof frame.layui.theme.changeTheme === 'function') {
					frame.layui.theme.changeTheme(frame, autoHead);
				}
			}
			catch (error) {
				// 静默处理所有错误，避免影响主页面功能
				// 只在开发环境下输出错误信息
				if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
					console.warn('Theme change error for frame:', error.message);
				}
			}
		}
	}

	theme.colorSet = function(color, second) {
		var style = '';
		style += '.light-theme .pear-nav-tree .layui-this a:hover,.light-theme .pear-nav-tree .layui-this,.light-theme .pear-nav-tree .layui-this a,.pear-nav-tree .layui-this a,.pear-nav-tree .layui-this{background-color: ' +color + '!important;}';
		style += '.pear-admin .layui-logo .title{color:' + color + '!important;}';
		style += '.pear-frame-title .dot,.pear-tab .layui-this .pear-tab-active{background-color: ' + color +'!important;}';
		style += '.bottom-nav li a:hover{background-color:' + color + '!important;}';
		style += '.pear-btn-primary {border: 1px solid ' + color + '!important;}';
		style += '.pear-admin .layui-header .layui-nav .layui-nav-bar{background-color: ' + color + '!important;}'
		style += '.ball-loader>span,.signal-loader>span {background-color: ' + color + '!important;}';
		style += '.layui-header .layui-nav-child .layui-this a{background-color:' + color +'!important;color:white!important;}';
		style += '#preloader{background-color:' + color + '!important;}';
		style += '.pearone-color .color-content li.layui-this:after, .pearone-color .color-content li:hover:after {border: ' +color + ' 3px solid!important;}';
		style += '.layui-nav .layui-nav-child dd.layui-this a, .layui-nav-child dd.layui-this{background-color:' + color + ';color:white;}';
		style += '.pear-social-entrance {background-color:' + color + '!important}';
		style += '.pear-admin .pe-collapse {background-color:' + color + '!important}';
		style += '.layui-fixbar li {background-color:' + color + '!important}';
		style += '.pear-btn-primary {background-color:' + color + '!important}';
		style += '.layui-input:focus,.layui-textarea:focus {border-color: ' + color + '!important;box-shadow: 0 0 0 3px ' + second + ' !important;}'
		style += '.layui-form-checkbox[lay-skin=primary]:hover span {background-color: initial;}'
		style += '.layui-form-checked[lay-skin=primary] i {border-color: ' + color + '!important;background-color: ' + color + ';}'
		style += '.layui-form-checked,.layui-form-checked:hover {border-color: ' + color + '!important;}'
		style += '.layui-form-checked span,.layui-form-checked:hover span {background-color: ' + color + ';}'
		style += '.layui-form-checked i,.layui-form-checked:hover i {color: ' + color + ';}'
		style += '.layui-form-onswitch { border-color: ' + color + '; background-color: ' + color + ';}'
		style += '.layui-form-radio>i:hover, .layui-form-radioed>i {color: ' + color + ';}'
		style += '.layui-laypage .layui-laypage-curr .layui-laypage-em{background-color:'+ color +'!important}'
		style += '.layui-tab-brief>.layui-tab-more li.layui-this:after, .layui-tab-brief>.layui-tab-title .layui-this:after{border-bottom: 3px solid '+color+'!important}'
		style += '.layui-tab-brief>.layui-tab-title .layui-this{color:'+color+'!important}'
		style += '.layui-progress-bar{background-color:'+color+'}';
		style += '.layui-elem-quote{border-left: 5px solid '+ color +'}';
		style += '.layui-timeline-axis{color:' + color + '}';
		style += '.layui-laydate .layui-this, .layui-laydate .layui-this div{background-color:'+color+'!important}';//变更
		style += 'xm-select .xm-label .xm-label-block{background-color:'+color+'!important}';//变更
		style += 'xm-select{border-color:#EEE !important}';//变更
		style += 'xm-select .xm-body .xm-option.selected .xm-option-icon{border-color:'+color+'!important;color:'+color+'!important;}';//变更
		style += 'xm-select .xm-body .xm-option .xm-option-icon{border-color:'+color+'!important;}';//变更
		style += 'xm-select > .xm-body .xm-option.selected.hide-icon{background-color:'+color+'!important;}';//变更
		style += 'xm-select > .xm-body .xm-toolbar .toolbar-tag:hover{color:'+color+'!important;}';//变更
		style += '.layui-layer-dialog .layui-layer-content .layui-icon-ok{color:'+color+'!important;}';//变更
		style += '.layui-layer-dialog .layui-layer-content .layui-icon-ok{color:'+color+'!important;}';//变更
		style += 'a{color:'+color+';opacity:.8}';//变更
		style += 'a:hover{color:'+color+';opacity:1}';//变更
		style += '.pear-this,.pear-text{color:' + color + '!important}';
		style += '.pear-back{background-color:'+ color +'!important}';
		style += '.pear-collapsed-pe{background-color:'+color+'!important}'
		style += '.layui-form-select dl dd.layui-this{color:'+color+'!important;}'
		style += '.tag-item-normal{background:'+color+'!important}';
		style += '.step-item-head.step-item-head-active{background-color:'+color+'}'
		style += '.step-item-head{border: 3px solid '+color+';}'
		style += '.step-item-tail i{background-color:'+color+'}'
		style += '.step-item-head{color:' + color + '}'
		style += 'div[xm-select-skin=normal] .xm-select-title div.xm-select-label>span i {background-color:'+color+'!important}'
		style += 'div[xm-select-skin=normal] .xm-select-title div.xm-select-label>span{border: 1px solid '+color+'!important;background-color:'+color+'!important}'
		style += 'div[xm-select-skin=normal] dl dd:not(.xm-dis-disabled) i{border-color:'+color+'!important}'
		style += 'div[xm-select-skin=normal] dl dd.xm-select-this:not(.xm-dis-disabled) i{color:'+color+'!important}'
		style += 'div[xm-select-skin=normal].xm-form-selected .xm-select, div[xm-select-skin=normal].xm-form-selected .xm-select:hover{border-color:'+color+'!important}'
		style += '.layui-layer-btn a:first-child{border-color:'+color+';background-color:'+color+'!important}';
		style += '.layui-form-checkbox[lay-skin=primary]:hover i{border-color:'+color+'!important}'
		style += '.pear-tab-menu .item:hover{background-color:'+color+'!important}'
		style += '.layui-form-danger:focus {border-color:#FF5722 !important}'
		style += '.pear-admin .user .layui-this a:hover{color:white!important}'
		style += '.pear-admin .user  a:hover{color:'+color+'!important}'
		style += '.pear-notice .layui-this{color:'+color+'!important}'
        style += '.layui-form-radio:hover *, .layui-form-radioed, .layui-form-radioed>i{color:' + color + ' !important}';
		style += '.pear-btn:hover {color: '+color+';background-color: ' + second + ';}'
		style += '.pear-btn-primary[plain] {color: '+ color +' !important;background: ' + second + ' !important;}'
		style += '.pear-btn-primary[plain]:hover {background-color: ' + color + '!important}'
		style += '.light-theme .pear-nav-tree .layui-this a:hover,.light-theme .pear-nav-tree .layui-this,.light-theme .pear-nav-tree .layui-this a {background-color:'+second+'!important;color:'+color+'!important;}'
		style += '.light-theme .pear-nav-tree .layui-this{ border-right: 3px solid '+color+'!important}'
		style += '.loader:after {background:'+color+'}'
		if(this.autoHead === true || this.autoHead === "true"){
			style += '.pear-admin.banner-layout .layui-header .layui-logo,.pear-admin .layui-header{border:none;background-color:' + color + '!important;}.pear-admin.banner-layout .layui-header .layui-logo .title,.pear-admin .layui-header .layui-nav .layui-nav-item>a{color:whitesmoke!important;}';
			style += '.pear-admin.banner-layout .layui-header{ box-shadow: 2px 0 6px rgb(0 21 41 / 35%) }'
			style += '.pear-admin .layui-header .layui-layout-control .layui-this *,.pear-admin.banner-layout .layui-header .layui-layout-control .layui-this *{ background-color: rgba(0,0,0,.1)!important;}'
		}
    style += '.menu-search-list li:hover,.menu-search-list li.this{background-color:'+ color +'}'
		// 添加仪表盘图标颜色支持
		style += '.stat-card .stat-icon{color:' + color + '!important;}';
		style += '.module-card .module-icon{color:' + color + '!important;}';
		style += '.doc-card .doc-icon{color:' + color + '!important;}';
		style += '.chart-container .chart-title i{color:' + color + '!important;}';
		style += '.system-info-card .layui-card-header i{color:' + color + '!important;}';
		style += '.welcome-banner{background-color:' + color + '!important;}';
		// 添加CSS变量支持
		style += ':root{--theme-color:' + color + ';--theme-second:' + second + ';}';
		var colorPane = $("#pear-admin-color");
		if(colorPane.length>0){
			colorPane.html(style);
		}else{
			$("head").append("<style id='pear-admin-color'>"+style+"</style>")
		}
	}

	exports(MOD_NAME, theme);
});
