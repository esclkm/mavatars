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
	<div>{FILEUPLOAD_INPUT}</div>
	<div>{FILEUPLOAD_INPUT}</div>
	<div>{FILEUPLOAD_INPUT}</div>
	<div>{FILEUPLOAD_INPUT}</div>
	<div>{FILEUPLOAD_INPUT}</div>
	<!-- END: UPLOAD -->
	
	<!-- BEGIN: CURLUPLOAD -->
	{PHP.L.mavatar_form_addcurl}
	<div>{CURLUPLOAD_INPUT}</div>
	<div>{CURLUPLOAD_INPUT}</div>	
	<div>{CURLUPLOAD_INPUT}</div>	
	<div>{CURLUPLOAD_INPUT}</div>	
	<div>{CURLUPLOAD_INPUT}</div>	
	<!-- END: CURLUPLOAD -->
</div>

<!-- END: MAIN -->