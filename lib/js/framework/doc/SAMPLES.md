
# Multi-File Upload Example

``` html
<div id="multifile_list" style="text-align:left; max-width:550px;">
	<input id="multifile_uploader" type="file" name="myvar[]" style="width:90%;">
</div>
<script type="text/javascript">
	SmartJS_BrowserUtils.CloneElement('multifile_uploader', 'multifile_list', 'file-input', 10);
</script>
```

