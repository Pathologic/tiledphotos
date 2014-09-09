<?php
if(!defined('MODX_BASE_PATH')){die();}
$tvname = isset($tvname) ? $tvname : 'photos';
$outerTpl = isset($outerTpl) ? $modx->getChunk($outerTpl) : '<div class="tiled-gallery type-rectangular">[+wrap+]</div>';
$rowTpl = isset($rowTpl) ? $modx->getChunk($rowTpl) : '<div class="gallery-row" style="width:[+width+]px; height:[+height+]px;margin-bottom:[+margin+]px;">[+wrap+]</div>';
$groupTpl = isset($groupTpl) ? $modx->getChunk($groupTpl) : '<div class="gallery-group images-[+count+]" style="width:[+width+]px; height:[+height+]px;">[+wrap+]</div>';
$itemTpl = isset($itemTpl) ? $modx->getChunk($itemTpl) : '<div class="tiled-gallery-item tiled-gallery-item-[+size+]"><a href="[+link+]" title="[+title+][+num+]"><img src="[+thumb+]" width="[+width+]" height="[+height+]" style="margin-bottom:[+margin+]px;" alt="[+title+]" /></a></div>';
if (isset($css) && $css) $modx->regClientCSS('<link rel="stylesheet" type="text/css" href="assets/snippets/tiledphotos/tiled-gallery.css">');
if (isset($id)) {
	$tvf = $modx->getTemplateVar($tvname,'*',$id);
	$tvv = $tvf['value'];
} else {
	$id = $modx->documentObject['id']; 
	$tvf = $modx->documentObject[$tvname];
	$tvv = $tvf[1];
}
if (!$tvv || $tvv=='[]') return;
$attachments=json_decode($tvv);

include_once(MODX_BASE_PATH.'assets/snippets/tiledphotos/tiledphotos.class.php');

$grouper = new themePacific_Jetpack_Tiled_Gallery_Grouper( $attachments, $modx );
themePacific_Jetpack_Tiled_Gallery_Shape::reset_last_shape();

$rows = '';
$num = 1;
foreach ( $grouper->grouped_images as $row ) {
	$groups = '';
	foreach( $row->groups as $group ) {
		$count = count( $group->images );
		$wrap='';
		foreach ( $group->images as $image ) {

			$size = 'large';
			if ( $image->width < 250 )
				$size = 'small';

			$image_title = $image->post_title;
			$link = $image->image_url;

			$img_src = $modx->runSnippet('phpthumb',array('input'=>$link,
				'options'=>'w='.$image->width.'&h='.$image->height.'&zc=C'
				)); 
			$fields = array('[+link+]','[+thumb+]','[+title+]','[+width+]','[+height+]','[+size+]','[+margin+]','[+num+]');
			$values = array($link,$img_src,$image_title,$image->width,$image->height,$size,$margin,$num++);
			$wrap .= str_replace($fields,$values,$itemTpl);
		}
		$fields = array('[+count+]','[+width+]','[+height+]','[+wrap+]');
		$values = array($count,$group->width,$group->height,$wrap);
		$groups .= str_replace($fields,$values,$groupTpl);
	}
	$fields = array('[+width+]','[+height+]','[+margin+]','[+wrap+]');
	$values = array($row->width,$row->height - $margin,$margin,$groups);
	$rows .= str_replace($fields,$values,$rowTpl);
}
$output = str_replace('[+wrap+]',$rows,$outerTpl);
return $output;