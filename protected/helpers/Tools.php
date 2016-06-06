<?php
class Tools {
	static public function maxFileSize() {
		return 200 * 1024 * 1024;
	}
	static public function inlineEdit($data, $field, $type='text') {
		return '<div class="inlineEdit" id="' . $data['id'] . '" field="'.$field.'" type="'.$type.'">' . $data[$field] . '</div>';
	}
	static public function freeFileName($mainName, $dir) {
		$newName = $mainName;
		$i = 1;
		while (file_exists($dir . '/' . $newName)) {
			$parts = explode('.', $mainName);
			$countParts = count($parts);
			if ($countParts > 1) {
				$extensionName = array_pop($parts);
				$newName = implode($parts) . '(' . $i . ').' . $extensionName;
			} else {
				$newName = '(' . $i . ')' . $mainName;
			}
			$i++;
		}
		return $newName;
	}
	static public function saveUploadedFile($fileupload, $dir, $old_file = '') {
		//print_r($fileupload);
		//Yii::app()->end();
        if ($fileupload instanceof CUploadedFile) {

			$newName = self::freeFileName(STranslate::transliter($fileupload->getName()), $dir);
			
			if (!file_exists($dir)) mkdir($dir,0775,true);
            $fileupload->saveAs($dir . '/' . $newName);
			
            //$this->file = $newName;
            if (!empty($old_file)) {
                $delete = $dir . '/' . $old_file;
                if (file_exists($delete)) {
                    unlink($delete);
                }
            }
        }
        if (empty($newName) && !empty($old_file)) {
            $newName = $old_file;
        }
		return $newName;
	}
	static public function uploadMaterials($folder, $premoderation = true) {
		Yii::import("ext.EAjaxUpload.qqFileUploader");
		if (!file_exists($folder)) {
			mkdir($folder, 0775, true);
		}
		$config['allowedExtensions'] = array('png', 'jpg', 'jpeg', 'gif', 'txt', 'doc', 'docx', 'pdf');
		$config['disAllowedExtensions'] = array('exe','scr');
		$sizeLimit = self::maxFileSize();// maximum file size in bytes
		$uploader = new qqFileUploader($config, $sizeLimit);
		$_GET['qqfile'] = self::freeFileName($_GET['qqfile'], $folder);
		if($premoderation && !(User::model()->isManager())) $_GET['qqfile']='#pre#'.$_GET['qqfile'];
		$result = $uploader->handleUpload($folder,true);
		$result['fileSize']=filesize($folder.$result['filename']);//GETTING FILE SIZE
		$result['fileName']=$result['filename'];//GETTING FILE NAME
		chmod($folder.$result['fileName'],0664);
		return $result;
	}
	static public function hint($val, $class){
		if ($val) { ?>
		<div class="<?=$class?>">
			<sup>?</sup>
			<div class="hint-block_content<?=(strlen($val)>180)?' hint-block_content-2x':''?>">
				<?=$val?>
			</div>
		</div>
		<?php }
	}
}
?>