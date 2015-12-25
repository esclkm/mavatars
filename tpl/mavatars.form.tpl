<!-- BEGIN: MAIN -->
<div class="mavatar_uploadform">
	
	

	<div class="uploadedfiles rows" id="">
	<!-- BEGIN: FILES -->	
		<!-- BEGIN: ROW -->
		<div class="uploadedfile col-md-3 marginbottom10">	

			<div class="img text-center drag">
				<!-- IF {MAVATAR.FILEEXT} == 'jpg' OR {MAVATAR.FILEEXT} == 'jpeg' OR {MAVATAR.FILEEXT} == 'png' OR {MAVATAR.FILEEXT} == 'gif' -->
				<a href="{MAVATAR.FILE}" target="_blank"  class="fancybox drag" rel="gallery1"><img src="{MAVATAR|cot_mav_thumb($this, 255, 191, auto)}" alt="{MAVATAR.FILENAME}.{MAVATAR.FILEEXT}" title="{MAVATAR.FILENAME}.{MAVATAR.FILEEXT}" class="img-thumbnail" /></a>
				<!-- ELSE -->
				<a href="{MAVATAR.FILE}" target="_blank" class="jumbotron text-center drag" style='width:255px;height:191px;display:inline-block;'>
					<h2><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></h2>[{MAVATAR.FILEEXT}]
				</a>
				<!-- ENDIF -->
			</div>
			<div class="row">	
				<div class="col-sm-2">{ENABLED}</div>
				
				<div class="col-sm-10 des">
					{FILEALT|cot_rc_modify('$this', 'class="form-control" placeholder="Alt" title="Alt"  ')}
					<div class="inp">{FILEDESCTEXT|cot_rc_modify('$this', 'class="form-control" title="ALT" placeholder="ALT"') }</div> 
					<div class="inp">{FILETEXTTEXT|cot_rc_modify('$this', 'class="form-control" title="TITLE" placeholder="TITLE"') }</div> 
				</div>
				
				
			</div>
			<div class="order hidden">
		    	{FILEORDER|cot_rc_modify('$this', 'class="form-control"')}
			</div>			

		</div>
		<!-- END: ROW -->	
	<!-- END: FILES -->
	</div>
	<div class="clearfix"></div>
	{PHP.L.mavatar_dnd_help}
	<!-- BEGIN: UPLOAD -->
	{PHP.L.mavatar_form_addfiles}
	<input type="file"  tabindex="-1" hidefocus="true" id="mavatar_file" name="mavatar_file[]" />
	<!-- END: UPLOAD -->
	
	<!-- BEGIN: AJAXUPLOAD -->

 
	<script>
		window.FileAPI = {
			  debug: false // debug mode
			, staticPath: '{PHP.cfg.plugins_dir}/mavatars/lib/FileAPI/' // path to *.swf
		};
	</script>	
	<script src="{PHP.cfg.plugins_dir}/mavatars/lib/sortable/Sortable.min.js"></script>
	<script src="{PHP.cfg.plugins_dir}/mavatars/lib/sortable/jquery.binding.js"></script>

	<script>
		jQuery(function ($){
			$('head').append('<style type="text/css">.drag{cursor:move;}</style>');
			$(".uploadedfiles").find('.uploadedfile [name^="mavatar_order"]' )
			$(".uploadedfiles").sortable({
				handle: ".drag",
				onSort: function (evt) {
					//$(evt.item).find('[name^="mavatar_order"]').val((evt.newIndex +1));
					$(".uploadedfiles").find('.uploadedfile [name^="mavatar_order"]' ).each(function(index) {
						$(this).val((index+1))
					});
				}
			});
		});
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
				url: '{FILEUPLOAD_URL_NOX}&x='+$('[name=x]').val(),
				autoUpload: true,
			//	accept: 'image/*',
				multiple: true,
				maxSize: FileAPI.MB*10, // max file size
				imageTransform: {
					// resize by max side
					maxWidth: 2000,
					maxHeight: 2600
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

</div>

<!-- END: MAIN -->