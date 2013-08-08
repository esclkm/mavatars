<!-- BEGIN: MAIN -->
<div>
	<!-- BEGIN: FILES -->
	{PHP.L.mavatar_form_addedfiles}
		<!-- BEGIN: ROW -->
		<div>{ENABLED}#{FILEORDER} <a href="{FILE}">{FILEORIGNAME}.{FILEEXT}</a> {PHP.L.Desc}{FILEDESC}{FILENEW}</div>
		<!-- END: ROW -->

	<!-- END: FILES -->
	
	<!-- BEGIN: UPLOAD -->
	{PHP.L.mavatar_form_addfiles}
	<!-- FOR {INDEX} IN {PHP.cfg.plugin.mavatars.items|range(1,$this)} -->
	<div>{FILEUPLOAD_INPUT}</div>
	<!-- ENDFOR -->
	<!-- END: UPLOAD -->
	
	<!-- BEGIN: CURLUPLOAD -->
	{PHP.L.mavatar_form_addcurl}
	<!-- FOR {INDEX} IN {PHP.cfg.plugin.mavatars.items|range(1,$this)} -->
	<div>{CURLUPLOAD_INPUT}</div>
	<!-- ENDFOR -->	
	<!-- END: CURLUPLOAD -->
</div>

<!-- END: MAIN -->