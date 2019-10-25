<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 为网站添加 darkreader.js，实现 <a href="https://darkreader.org/">Dark Reader</a> 的相关功能
 *
 * @package DarkReader
 * @author 南宫小骏
 * @version 1.0.0
 * @link https://jacky.live
 */
class DarkReader_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->footer = array('DarkReader_Plugin', 'footerJS');
        Typecho_Plugin::factory('admin/footer.php')->end = array('DarkReader_Plugin', 'footerJS');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    { }

    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $themeOptions = new Typecho_Widget_Helper_Form_Element_Textarea(
            'themeOptions',
            NULL,
            '{}',
            _t('主题选项'),
            _t(
                '值为JSON格式的字符串，可用的值有：
<pre class="description">{
    mode: 1,            // 1 - 黑暗模式, 0 - 明亮模式
    brightness: 100,    // 亮度（0 - 100+）
    contrast: 100,      // 对比度（0 - 100+）
    grayscale: 0,       // 灰度（0 - 100）
    sepia: 0,           // 棕褐色度（0 - 100）
    useFont: false,     // 是否使用自定义字体
    fontFamily: "",     // 自定义的字体名称
    textStroke: 0,      // 文字描边（0 - 1px）
}</pre>'
            )
        );
        $form->addInput($themeOptions);

        $fixes  = new Typecho_Widget_Helper_Form_Element_Textarea(
            'fixes',
            NULL,
            '',
            _t('修复项目'),
            _t('动态生成主题时的个别修复项目，具体用法参见<a href="https://github.com/darkreader/darkreader#how-to-contribute">官方文档（英文）</a>')
        );
        $form->addInput($fixes);

        $isIFrame = new Typecho_Widget_Helper_Form_Element_Radio(
            'isIFrame',
            array(
                'true' => '是',
                'false' => '否',
            ),
            'false',
            _t('是否 IFrame'),
            _t('转换的页面是否被包含在 IFrame 中')
        );
        $form->addInput($isIFrame);
    }

    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    { }

    /**
     * 插入并初始化 Dark Reader
     * 
     * @access public
     * @return void
     */
    public static function footerJS($conent)
    {
        $options = Helper::options();
        $pluginRoot = Typecho_Common::url('DarkReader', $options->pluginUrl);

        $darkreader = Typecho_Widget::widget('Widget_Options')->plugin('DarkReader');

        $cls = "off" . ($conent ? " front" : " admin");

        echo <<<CODE
        <div id="darkreader-btn-light" class="$cls" title="开灯"></div>
        <link rel="stylesheet" href="$pluginRoot/style.css">
        <script src="https://cdn.jsdelivr.net/npm/js-polyfills" nomodule ></script>
        <script src="https://cdn.jsdelivr.net/npm/darkreader"></script>
        <script>
            !function() {
                var params = [$darkreader->themeOptions, '$darkreader->fixes', $darkreader->isIFrame];
                var el = document.getElementById('darkreader-btn-light');

                el.onclick = function() {
                    var className = el.className, title = '';

                    if (className.indexOf('on ') > -1) {
                        className = className.replace('on ', 'off ');
                        title = '开灯';
                        DarkReader.enable(params[0], params[1], params[2]);
                    } else {
                        className = className.replace('off ', 'on ');
                        title = '关灯';
                        DarkReader.disable();
                    }
                    
                    el.className = className;
                    el.title = title;
                };
                DarkReader.enable(params[0], params[1], params[2]);
            }();
        </script>
        CODE;
    }
}
