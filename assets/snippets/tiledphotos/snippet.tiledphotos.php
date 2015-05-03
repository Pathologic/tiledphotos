<?php
if(!defined('MODX_BASE_PATH')){die();}
include_once (MODX_BASE_PATH.'assets/snippets/DocLister/lib/DLTemplate.class.php');
$DLTemplate = DLTemplate::getInstance($modx);
$params['api'] = 'sg_image,sg_title,sg_properties';
$data = $modx->runSnippet('sgLister',$params);
$attachments=json_decode($data,true);
if (empty($attachments)) return $DLTemplate->parseChunk($noneTPL,array());

//вся галерея
$outerTpl = isset($outerTpl) ? $DLTemplate->getChunk($outerTpl) : '@CODE: <div class="tiled-gallery type-rectangular">[+wrap+]</div>';
//строка
$rowTpl = isset($rowTpl) ? $DLTemplate->getChunk($rowTpl) : '@CODE: <div class="gallery-row" style="width:100%;">[+wrap+]</div>';
//группа картинок
$groupTpl = isset($groupTpl) ? $DLTemplate->getChunk($groupTpl) : '@CODE: <div class="gallery-group images-[+count+]" style="width:[+rel.width+]; ">[+wrap+]</div>';
//картинка в группе
$itemTpl = isset($itemTpl) ? $DLTemplate->getChunk($itemTpl) : '@CODE: <div class="tiled-gallery-item tiled-gallery-item-[+size+]"><a href="[+link+]" title="[+title+][+num+]"><img src="[+thumb+]" style="margin-bottom:[+margin+]px;" alt="[+title+]" /></a></div>';
//коэффициент увеличение превьюшки, хз зачем он был нужен
$k = isset($k) ? $k : 1;
//дополнительные опции для превьюшки
$thumbOptions = isset($thumbOptions) ? $thumbOptions : 'zc=C';
if (isset($css) && $css) $modx->regClientCSS('<link rel="stylesheet" type="text/css" href="assets/snippets/tiledphotos/tiled-gallery.css">');

include_once(MODX_BASE_PATH.'assets/snippets/tiledphotos/tiledphotos.class.php');
$modx->event->params = $params;
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

			$imageUrl = $image->image_url;

			$img_src = $modx->runSnippet('phpthumb',array(
				'input'=>$imageUrl,
				'options'=>'w='.round($k * $image->width).'&h='.round($k * $image->height).$thumbOptions
			)); 
			$wrap .= $DLTemplate->parseChunk($itemTpl,array(
				'image' => $imageUrl,
				'thumb'	=> $img_src,
				'title' => $image->post_title,
				'width'	=> $image->width,
				'height'=> $image->height,
				'size'	=> $image->width < 250 ? 'small' : 'large',
				'margin'=> $margin,
				'index'	=> $num++
			));
		}
		$groups .= $DLTemplate->parseChunk($groupTpl,array(
			'count'	=> $count,
			'width'	=> $group->width.'px',
			'rel.width' => str_replace(',','.',round($group->width / $width * 100, 3)).'%',
			'height'=> $group->height,
			'wrap'	=> $wrap
		));
	}
	$rows .= $DLTemplate->parseChunk($rowTpl,array(
		'width'	=> $row->width,
		'height'=> $row->height - $margin,
		'margin'=> $margin,
		'wrap'	=> $groups
	));
}
$output = $DLTemplate->parseChunk($outerTpl,array('wrap'=>$rows));
return $output;