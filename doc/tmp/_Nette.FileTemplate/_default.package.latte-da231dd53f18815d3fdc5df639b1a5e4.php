<?php //netteCache[01]000377a:2:{s:4:"time";s:21:"0.38284100 1408522114";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:19:"Nette\Caching\Cache";i:1;s:9:"checkFile";}i:1;s:57:"/opt/local/lib/php/apigen/templates/default/package.latte";i:2;i:1347110810;}i:1;a:3:{i:0;a:2:{i:0;s:19:"Nette\Caching\Cache";i:1;s:10:"checkConst";}i:1;s:25:"Nette\Framework::REVISION";i:2;s:28:"$WCREV$ released on $WCDATE$";}}}?><?php

// source file: /opt/local/lib/php/apigen/templates/default/package.latte

?><?php
// prolog Nette\Latte\Macros\CoreMacros
list($_l, $_g) = Nette\Latte\Macros\CoreMacros::initRuntime($template, 'x1m7uj8u8p')
;
// prolog Nette\Latte\Macros\UIMacros
//
// block title
//
if (!function_exists($_l->blocks['title'][] = '_lbf34c2d0c1d_title')) { function _lbf34c2d0c1d_title($_l, $_args) { extract($_args)
;if ($package != 'None'): ?>Package <?php echo Nette\Templating\Helpers::escapeHtml($package, ENT_NOQUOTES) ;else: ?>
No package<?php endif ;
}}

//
// block content
//
if (!function_exists($_l->blocks['content'][] = '_lb70904324bf_content')) { function _lb70904324bf_content($_l, $_args) { extract($_args)
?><div id="content" class="package">
	<h1><?php if ($package != 'None'): ?>Package <?php echo $template->packageLinks($package, false) ;else: ?>
No package<?php endif ?></h1>

<?php if ($subpackages): ?>	<table class="summary" id="packages">
	<caption>Packages summary</caption>
<?php $iterations = 0; foreach ($subpackages as $package): ?>	<tr>
		<td class="name"><a href="<?php echo htmlSpecialChars($template->packageUrl($package)) ?>
"><?php echo Nette\Templating\Helpers::escapeHtml($package, ENT_NOQUOTES) ?></a></td>
	</tr>
<?php $iterations++; endforeach ?>
	</table>
<?php endif ?>

<?php Nette\Latte\Macros\CoreMacros::includeTemplate('@elementlist.latte', $template->getParameters(), $_l->templates['x1m7uj8u8p'])->render() ?>
</div>
<?php
}}

//
// end of blocks
//

// template extending and snippets support

$_l->extends = '@layout.latte'; $template->_extended = $_extended = TRUE;


if ($_l->extends) {
	ob_start();

} elseif (!empty($_control->snippetMode)) {
	return Nette\Latte\Macros\UIMacros::renderSnippets($_control, $_l, get_defined_vars());
}

//
// main template
//
 $active = 'package' ?>

<?php if ($_l->extends) { ob_end_clean(); return Nette\Latte\Macros\CoreMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render(); }
call_user_func(reset($_l->blocks['title']), $_l, get_defined_vars())  ?>


<?php call_user_func(reset($_l->blocks['content']), $_l, get_defined_vars()) ; 