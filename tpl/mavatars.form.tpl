<!-- BEGIN: MAIN -->
<div class="mavatar_uploadform">
	<div class="uploadedfiles rows">
	<!-- BEGIN: FILES -->	
		<!-- BEGIN: ROW -->
		<div class="uploadedfile col-md-3 marginbottom10">	

			<div class="img text-center">
				<!-- IF {MAVATAR.FILEEXT} == 'jpg' OR {MAVATAR.FILEEXT} == 'jpeg' OR {MAVATAR.FILEEXT} == 'png' OR {MAVATAR.FILEEXT} == 'gif' -->
				<a href="{MAVATAR.FILE}" target="_blank"  class="fancybox" rel="gallery1"><img src="{MAVATAR|cot_mav_thumb($this, 255, 191, auto)}" alt="{MAVATAR.FILENAME}.{MAVATAR.FILEEXT}" title="{MAVATAR.FILENAME}.{MAVATAR.FILEEXT}" class="img-thumbnail" /></a>
				<!-- ELSE -->
				<a href="{MAVATAR.FILE}" target="_blank" class="jumbotron text-center" style='width:255px;height:191px;display:inline-block;'>
					<h2><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></h2>[{MAVATAR.FILEEXT}]
				</a>
				<!-- ENDIF -->
			</div>
			<div class="des">
				<div class="inp">{FILEDESCTEXT|cot_rc_modify('$this', 'class="form-control"')}</div> 
			</div>			
			<div class="order input-group">
				<span class="input-group-addon">
		        	Порядок
		    	</span>
		    	{FILEORDER|cot_rc_modify('$this', 'class="form-control"')}
				<span class="input-group-addon">
		        	 Доступно {ENABLED}
		    	</span>		    	
			</div>			

		</div>
		<!-- END: ROW -->	
	<!-- END: FILES -->
	</div>
	<div class="clearfix"></div>

	<!-- BEGIN: UPLOAD -->
	{PHP.L.mavatar_form_addfiles}
	<!-- FOR {INDEX} IN {PHP.cfg.plugin.mavatars.items|range(1,$this)} -->
	<div>{FILEUPLOAD_INPUT}</div>
	<!-- ENDFOR -->
	<!-- END: UPLOAD -->
	
	<!-- BEGIN: AJAXUPLOAD -->

 
	<script>
		window.FileAPI = {
			  debug: false // debug mode
			, staticPath: '{PHP.cfg.plugins_dir}/mavatars/lib/FileAPI/' // path to *.swf
		};
	</script>	
	
	<script src="{PHP.cfg.plugins_dir}/mavatars/lib/FileAPI/FileAPI.min.js"></script>
	<script src="{PHP.cfg.plugins_dir}/mavatars/lib/FileAPI/FileAPI.exif.js"></script>
	<script src="{PHP.cfg.plugins_dir}/mavatars/lib/jquery.fileapi.min.js"></script>
	<div class="js-fileapi-error">
		
	</div>
	<div id="uploader">
		
		<div class="js-fileapi-wrapper">
			<input type="file"  tabindex="-1" hidefocus="true" id="mavatar_file" name="mavatar_file[]" />
		</div>
		<div data-fileapi="active.show" class="progress">
			<div data-fileapi="progress" class="progress__bar"></div>
		</div>
	</div>
	<script>
		jQuery(function ($){
			$('#uploader').fileapi({
				url: '{FILEUPLOAD_URL}',
				autoUpload: true,
			//	accept: 'image/*',
				multiple: true,
				maxSize: FileAPI.MB*10, // max file size
				imageTransform: {
					// resize by max side
					maxWidth: 1600,
					maxHeight: 1600
				},
				onFileComplete: function (evt, uiEvt){
					var file = uiEvt.file;
					var data = uiEvt.result;
					if (data.success == 1) {
					//	uploadobj.remove();
						var decoded = $('<textarea/>').html(data.form).val();
							$('.uploadedfiles').append(decoded);
						}
					else {
						var error= '';
						if(data.error !== undefined)
						{

							error = data.error;
						}
						else
						{
							error = data;
						}
						$('.js-fileapi-error').append('<div class="alert alert-danger alert-dismissible" role="alert">'+
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
								'<strong>' + file.name + '</strong>: '+ error + '</div>');
					}
				}
			});
		});
	</script>
	
	<!-- END: AJAXUPLOAD -->
	
	<!-- BEGIN: CURLUPLOAD -->
	{PHP.L.mavatar_form_addcurl}
	<!-- FOR {INDEX} IN {PHP.cfg.plugin.mavatars.items|range(1,$this)} -->
	<div>{CURLUPLOAD_INPUT}</div>
	<!-- ENDFOR -->	
	<!-- END: CURLUPLOAD -->
</div>

<!-- END: MAIN -->