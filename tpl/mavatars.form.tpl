<!-- BEGIN: MAIN -->
<div class="mavatar_uploadform">
	<div class="uploadedfiles">
	<!-- BEGIN: FILES -->	
		<!-- BEGIN: ROW -->
		<div>{ENABLED}#{FILEORDER} <a href="{FILE}">{FILEORIGNAME}.{FILEEXT}</a> {PHP.L.Desc}{FILEDESC}{FILENEW}</div>
		<!-- END: ROW -->	
	<!-- END: FILES -->
	</div>

	<!-- BEGIN: UPLOAD -->
	{PHP.L.mavatar_form_addfiles}
	<!-- FOR {INDEX} IN {PHP.cfg.plugin.mavatars.items|range(1,$this)} -->
	<div>{FILEUPLOAD_INPUT}</div>
	<!-- ENDFOR -->
	<!-- END: UPLOAD -->
	
	<!-- BEGIN: AJAXUPLOAD -->
	
		<script type="text/javascript" src="{PHP.cfg.plugins_dir}/mavatars/js/pekeupload.js"></script>
		<script type="text/javascript">
		$(document).ready(function() {
			$('head').append('<link href="{PHP.cfg.plugins_dir}/mavatars/js/pekeupload.css" type="text/css" rel="stylesheet" />');
			$("#mavatar_file").pekeUpload({ url:'{FILEUPLOAD_URL}', 
				btnText:'{PHP.L.mavatar_form_addfiles}',
				onFileSuccess: function(file,data){
					var decoded = $('<textarea/>').html(data.form).val();
					$('.uploadedfiles').append(decoded);
            }
			});
//upload.php?r=mavatars&ext=page&cat=ceiling&code=96
			//url:'{FILEUPLOAD_URL}',
			});
		</script>
	<div id="mavatarupload"><input type="file" value="" id="mavatar_file" name="mavatar_file"></div>
	<!-- END: AJAXUPLOAD -->
	
	<!-- BEGIN: CURLUPLOAD -->
	{PHP.L.mavatar_form_addcurl}
	<!-- FOR {INDEX} IN {PHP.cfg.plugin.mavatars.items|range(1,$this)} -->
	<div>{CURLUPLOAD_INPUT}</div>
	<!-- ENDFOR -->	
	<!-- END: CURLUPLOAD -->
</div>

<!-- END: MAIN -->