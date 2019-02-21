<!DOCTYPE html>
<html>
<head>
	<title>Update Checker Manager</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<!-- jQuery library -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<!-- Latest compiled JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<!-- Include Font Awesome Stylesheet in Header -->
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
</head>
<body>
	<div class="container">
		<header>
			<div class="page-header">
			  	<h1>Update Checker Manager <small>v1.0</small></h1>
			</div>
		</header>
		<?php 
			//Functions
			function wd_message_alert($message = 'Error', $type = 'danger'){ 
				//type : success, info, warning, danger
				echo '<div class="alert alert-'.$type.'">'.$message.'</div>';
			};

			// Snippet from PHP Share: http://www.phpshare.org

		    function wd_format_size_units($bytes){
		        if ($bytes >= 1073741824){
		            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
		        }
		        elseif ($bytes >= 1048576){
		            $bytes = number_format($bytes / 1048576, 2) . ' MB';
		        }
		        elseif ($bytes >= 1024){
		            $bytes = number_format($bytes / 1024, 2) . ' KB';
		        }
		        elseif ($bytes > 1){
		            $bytes = $bytes . ' bytes';
		        }
		        elseif ($bytes == 1){
		            $bytes = $bytes . ' byte';
		        }
		        else{
		            $bytes = '0 bytes';
		        }
		        return $bytes;
			}

			function wd_upload_zip_file($target_dir, $field_name) { 
				if (!is_array($_FILES[$field_name]) || !is_dir($target_dir)) return;
				if ($_FILES[$field_name]["name"] == '') return;
			   	$file_name 		= basename($_FILES[$field_name]["name"]);
			   	if ($file_name == '') return;
			   	$target_file 	= $target_dir . $file_name;
				$upload_status 	= 1;
				$file_type 		= strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
				$message   		= "<strong>The results of the file upload from \"$field_name\": </strong><br>";
				// Allow certain file formats
				if($file_type != "zip") {
					$message   .= "- Only ZIP files are allowed.<br>";
					$upload_status = 0;
				}
				// Check if $upload_status is set to 0 by an error
				if ($upload_status == 0) {
					$message   .= "- The file <strong>". $file_name. "</strong> was not uploaded.";
					wd_message_alert($message);
				} else {
					// if everything is ok, try to upload file
					if (move_uploaded_file($_FILES[$field_name]["tmp_name"], $target_file)) {
						wd_message_alert("- The file: <strong>". $file_name." (".wd_format_size_units($_FILES[$field_name]['size']). ")</strong> has been uploaded to <strong>$target_dir</strong>.", 'success');
					} else {
						wd_message_alert("- There was an error uploading your file.");
					}
				}
			}

			function wd_upload_multi_file($target_dir, $field_name) { 
				if (!is_array($_FILES[$field_name]) || !is_dir($target_dir)) return;
				if (count($_FILES[$field_name]["name"]) == 0) return;
				$file_count = count($_FILES[$field_name]["name"]);
				for($i = 0; $i < $file_count; $i++) {
			        $file_name 		= basename($_FILES[$field_name]["name"][$i]);
			        if ($file_name == '') return;
				   	$target_file 	= $target_dir . $file_name;
					$upload_status 	= 1;
					$file_type 		= strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
					$list_docs_file = array('plugin_changelog.html', 'plugin_desc.html', 'plugin_install.html', 'theme_changelog.html');
					$message   		= "<strong>The results of the file upload from \"$field_name\": </strong><br>";
					// Allow certain file formats
					if($file_type != "html") {
						$message   .= "- Only HTML files are allowed.<br>";
						$upload_status = 0;
					}
					// Check docs file name
					if($file_type == "html" && !in_array($file_name, $list_docs_file)) {
						$message   .= "- Document filename must be: <strong>".implode(', ', $list_docs_file).'</strong><br>';
						$upload_status = 0;
					}
					// Check if $upload_status is set to 0 by an error
					if ($upload_status == 0) {
						$message   .= "- The document file: <strong>". $file_name. "</strong> was not uploaded.";
						wd_message_alert($message);
					} else {
						// if everything is ok, try to upload file
						if (move_uploaded_file($_FILES[$field_name]["tmp_name"][$i], $target_file)) {
							wd_message_alert("- The file <strong>". $file_name." (".wd_format_size_units($_FILES[$field_name]['size'][$i]). ")</strong> has been uploaded to <strong>$target_dir</strong>.", 'success');
						} else {
							wd_message_alert("- There was an error uploading your file.");
						}
					}
			    }
			}

			function wd_folder_copy($dir_src,$dir_new) { 
			    $dir = opendir($dir_src); 
			    @mkdir($dir_new); 
			    while(false !== ( $file = readdir($dir)) ) { 
			        if (( $file != '.' ) && ( $file != '..' )) { 
			            if ( is_dir($dir_src . '/' . $file) ) { 
			                wd_folder_copy($dir_src . '/' . $file,$dir_new . '/' . $file); 
			            } 
			            else { 
			                copy($dir_src . '/' . $file,$dir_new . '/' . $file); 
			            } 
			        } 
			    } 
			    closedir($dir); 
			}

			function delete_directory($dirname) {
				$dir_handle = false;
			    if (is_dir($dirname)){
			        $dir_handle = opendir($dirname);
			    }else{
			    	wd_message_alert("- The directory <strong>". $dirname. "</strong> does not exist.", 'warning');
			    }
			    if (!$dir_handle)
			        return false;
			    while($file = readdir($dir_handle)) {
		           if ($file != "." && $file != "..") {
		                if (!is_dir($dirname."/".$file))
		                    unlink($dirname."/".$file);
		                else
		                    delete_directory($dirname.'/'.$file);
		            }
			    }
			    closedir($dir_handle);
			    @rmdir($dirname);
			    wd_message_alert("- The directory <strong>". $dirname. "</strong> has been deleted.", 'success');
			    return true;
			}
		?>
		<div class="main-container">
			<div class="panel panel-default">
				<div class="panel-body">
					<h4>FILE MANAGER</h4>
					<?php if (empty($_POST) && empty($_FILES)): ?>
						<form method="post" enctype="multipart/form-data">
							<div class="alert alert-info">
							  	<strong>Notice!</strong> Note: The old files will be replaced by the new files in the theme you selected.
							</div>
							<div class="form-group">
							    <label for="upload_theme_file">Select Theme File (*.zip)</label>
							    <input type="file" class="form-control-file" name="upload_theme_file" id="upload_theme_file">
							    <small class="form-text text-muted">Exam: <strong>thefuture.zip</strong></small>
							</div>
							<div class="form-group">
							    <label for="upload_plugin_file">Select Plugin File (*.zip)</label>
							    <input type="file" class="form-control-file" name="upload_plugin_file" id="upload_plugin_file">
							    <small class="form-text text-muted">Exam: <strong>wd_packages.zip</strong></small>
							</div>
							<div class="form-group">
							    <label for="upload_docs_file">Doccument File (*.html)</label>
							    <input type="file" class="form-control-file" name="upload_docs_file[]" id="upload_docs_file" multiple>
							    <small class="form-text text-muted">Files names are allowed: <strong>plugin_changelog.html</strong>, <strong>plugin_desc.html</strong>, <strong>plugin_install.html</strong>, <strong>theme_changelog.html</strong></small>
							</div>
							<div class="form-group">
							    <label for="select_theme">Select Theme</label>
							    <?php 
							    	$dir    		= './packages';
									$list_folder 	= array_filter(glob($dir . '/*' , GLOB_ONLYDIR));
									$list_folder 	= !empty($list_folder) ? array_filter($list_folder) : array();
									$list_folder[]	= 'Add_To_New_Theme_Folder';
								?>
							    <select class="form-control" id="select_theme" name="select_theme">
							    	<?php if (!empty($list_folder)): ?>
							    		<?php foreach ($list_folder as $key): ?>
							    			<?php 
								    			$folder_name = str_replace($dir, '', $key);
								    			$folder_name = str_replace('/', '', $folder_name); 
								    			$selected 	 = isset($_GET['theme']) && $_GET['theme'] == $folder_name ? "selected=selected" : '';
							    			?>
							    			<option <?php echo $selected; ?> value="<?php echo $folder_name; ?>"><?php echo $folder_name; ?></option>
							    		<?php endforeach ?>
							    	<?php endif ?>
							    </select>
							    <small class="form-text text-muted">Which theme do you want to update?</small>
							</div>
							<div class="form-group new_theme_folder_name_wraper hide">
							    <label for="new_theme_folder_name">New Theme Folder Name</label>
							    <input type="text" class="form-control" id="new_theme_folder_name" name="new_theme_folder_name" placeholder="Exam: thefuture">
							</div>
							<button type="submit" class="btn btn-primary">Submit</button>
						</form>
					<?php else: ?>
						<?php 
							//var_dump($_FILES);
							$theme_slug 	= (isset($_POST['select_theme']) && $_POST['select_theme'] != 'Add_To_New_Theme_Folder') ? $_POST['select_theme'] : $_POST['new_theme_folder_name'];
							if ($theme_slug == '') 
								wd_message_alert("- Please enter the theme slug before uploading the file.", 'warning');
							$parkages_dir 	= "./packages/$theme_slug/packages/";
							$docs_dir 		= "./changelog/$theme_slug/";

							//Copy file from template to new theme folder.
							if (!is_dir("./packages/$theme_slug")) {
								wd_folder_copy("./template/packages", "./packages/$theme_slug");
							}
							if (!is_dir("./changelog/$theme_slug")) {
								wd_folder_copy("./template/changelog", "./changelog/$theme_slug");
							}
							//Create doccument folder if not exist.
							if (!is_dir($docs_dir)) {
								@mkdir($docs_dir);
							}

							if (!empty($_FILES)) {
								foreach ($_FILES as $key => $value) {
									if ($key == 'upload_docs_file') {
										wd_upload_multi_file($docs_dir, $key);
									}else{
										wd_upload_zip_file($parkages_dir, $key);
									}
								}
							}
						?>
						<a href="#" onclick="history.go(-1);"><i class="fa fa-reply" aria-hidden="true"></i> Back</a>
					<?php endif ?>
				</div>
			</div>
			<?php 
				//Delete directory
				if (isset($_GET['delete'])) {
					echo '<div class="panel panel-default"><div class="panel-body">';
					echo '<h4>DELETE DIRECTORY</h4>';
					$dir_name	 	= $_GET['delete'];
					$parkages_dir 	= "./packages/$dir_name/";
					$docs_dir 		= "./changelog/$dir_name/";
					delete_directory($parkages_dir);
					delete_directory($docs_dir);
					echo '</div></div>';
				}
			?>
		</div>
		<footer>
			<hr>
			<div class="text-center center-block page-footer">
				<p class="txt-railway">- Cao Vuong © WPDance -</p>
				<a target="_blank" href="https://www.facebook.com/hoangcaovuong"><i id="social-fb" class="fa fa-facebook-square fa-3x social"></i></a>
				<a target="_blank" href="https://twitter.com/hoangcaovuong"><i id="social-tw" class="fa fa-twitter-square fa-3x social"></i></a>
				<a target="_blank" href="https://plus.google.com/u/0/+WeLoveSonTungMTPChannel"><i id="social-gp" class="fa fa-google-plus-square fa-3x social"></i></a>
				<a target="_blank" href="mailto:hoangcaovuong92@gmail.com"><i id="social-em" class="fa fa-envelope-square fa-3x social"></i></a>
			</div>
			<hr>
		</footer>
	</div>

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			jQuery('#select_theme').on('change', function(){
				var value = jQuery(this).val();
				if (value === 'Add_To_New_Theme_Folder') {
					jQuery('.new_theme_folder_name_wraper').removeClass('hide');
				}else{
					jQuery('.new_theme_folder_name_wraper').addClass('hide');
				}
			}).trigger('change');;
		});
	</script>
</body>
</html>