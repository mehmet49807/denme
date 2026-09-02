<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$adminRoot = dirname(__DIR__).'/admin.gonulkoprusu.com';
if (! is_dir($adminRoot)) {
    $adminRoot = '/home/gonulkop/admin.gonulkoprusu.com';
}
if (! is_dir($adminRoot)) {
    http_response_code(500);
    exit("admin root missing\n");
}

echo "Admin panel restoration\n";
echo "admin_root=$adminRoot\n";

// Write core files
$files = json_decode(<<<'JSON'
{"index.php": "PD9waHAKCnVzZSBJbGx1bWluYXRlXEh0dHBcUmVxdWVzdDsKCmRlZmluZSgnTEFSQVZFTF9TVEFSVCcsIG1pY3JvdGltZSh0cnVlKSk7CgovLyBDYWNoZSBidXN0IG9uIGRlcGxveQokY2FjaGVCdXN0TWFya2VyID0gX19ESVJfXy4nL3N0b3JhZ2UvZnJhbWV3b3JrL2NhY2hlLy5kZXBsb3lfY2FjaGVfYnVzdF92MTQnOwppZiAoISBpc19maWxlKCRjYWNoZUJ1c3RNYXJrZXIpKSB7CiAgICBmb3JlYWNoIChbJ3JvdXRlcy12Ny5waHAnLCAnY29uZmlnLnBocCcsICdldmVudHMucGhwJ10gYXMgJG5hbWUpIHsKICAgICAgICAkZmlsZSA9IF9fRElSX18uJy9ib290c3RyYXAvY2FjaGUvJy4kbmFtZTsKICAgICAgICBpZiAoaXNfZmlsZSgkZmlsZSkpIHsKICAgICAgICAgICAgQHVubGluaygkZmlsZSk7CiAgICAgICAgfQogICAgfQogICAgZm9yZWFjaCAoZ2xvYihfX0RJUl9fLicvc3RvcmFnZS9mcmFtZXdvcmsvdmlld3MvKi5waHAnKSA/OiBbXSBhcyAkdmlld0ZpbGUpIHsKICAgICAgICBpZiAoaXNfZmlsZSgkdmlld0ZpbGUpKSB7CiAgICAgICAgICAgIEB1bmxpbmsoJHZpZXdGaWxlKTsKICAgICAgICB9CiAgICB9CiAgICBAbWtkaXIoZGlybmFtZSgkY2FjaGVCdXN0TWFya2VyKSwgMDc1NSwgdHJ1ZSk7CiAgICBAZmlsZV9wdXRfY29udGVudHMoJGNhY2hlQnVzdE1hcmtlciwgZGF0ZSgnYycpKTsKfQoKaWYgKGZ1bmN0aW9uX2V4aXN0cygnb3BjYWNoZV9pbnZhbGlkYXRlJykpIHsKICAgIGZvcmVhY2ggKFsKICAgICAgICBfX0RJUl9fLicvcm91dGVzL3dlYi5waHAnLAogICAgICAgIF9fRElSX18uJy9yb3V0ZXMvYWRtaW5sb2dpbi5waHAnLAogICAgICAgIF9fRElSX18uJy9yb3V0ZXMvYWRtaW5fc3ViZG9tYWluLnBocCcsCiAgICAgICAgX19ESVJfXy4nL3JvdXRlcy9hcGkucGhwJywKICAgICAgICBfX0RJUl9fLicvYm9vdHN0cmFwL2FwcC5waHAnLAogICAgXSBhcyAkaW52YWxpZGF0ZSkgewogICAgICAgIGlmIChpc19maWxlKCRpbnZhbGlkYXRlKSkgewogICAgICAgICAgICBAb3BjYWNoZV9pbnZhbGlkYXRlKCRpbnZhbGlkYXRlLCB0cnVlKTsKICAgICAgICB9CiAgICB9Cn0KCmlmIChmaWxlX2V4aXN0cygkbWFpbnRlbmFuY2UgPSBfX0RJUl9fLicvc3RvcmFnZS9mcmFtZXdvcmsvbWFpbnRlbmFuY2UucGhwJykpIHsKICAgIHJlcXVpcmUgJG1haW50ZW5hbmNlOwp9CgokdmVuZG9yQXV0b2xvYWQgPSBfX0RJUl9fLicvdmVuZG9yL2F1dG9sb2FkLnBocCc7CmlmICghIGlzX2ZpbGUoJHZlbmRvckF1dG9sb2FkKSkgewogICAgLy8gVHJ5IHRvIGNvcHkgdmVuZG9yIGZyb20gd2ViLXNpdGUKICAgICR3ZWJWZW5kb3IgPSBkaXJuYW1lKF9fRElSX18pLicvZ29udWxrb3BydXN1LmNvbS92ZW5kb3IvYXV0b2xvYWQucGhwJzsKICAgIGlmICghIGlzX2ZpbGUoJHdlYlZlbmRvcikpIHsKICAgICAgICAkd2ViVmVuZG9yID0gZGlybmFtZShfX0RJUl9fKS4nL3B1YmxpY19odG1sL3ZlbmRvci9hdXRvbG9hZC5waHAnOwogICAgfQogICAgaWYgKCEgaXNfZmlsZSgkd2ViVmVuZG9yKSkgewogICAgICAgICR3ZWJWZW5kb3IgPSAnL2hvbWUvZ29udWxrb3AvZ29udWxrb3BydXN1LmNvbS92ZW5kb3IvYXV0b2xvYWQucGhwJzsKICAgIH0KICAgIGlmICghIGlzX2ZpbGUoJHdlYlZlbmRvcikpIHsKICAgICAgICAkd2ViVmVuZG9yID0gJy9ob21lL2dvbnVsa29wL3B1YmxpY19odG1sL3ZlbmRvci9hdXRvbG9hZC5waHAnOwogICAgfQogICAgaWYgKGlzX2ZpbGUoJHdlYlZlbmRvcikpIHsKICAgICAgICAvLyBTeW1saW5rIHZlbmRvciBmcm9tIHdlYi1zaXRlCiAgICAgICAgJHdlYlZlbmRvckRpciA9IGRpcm5hbWUoZGlybmFtZSgkd2ViVmVuZG9yKSk7CiAgICAgICAgaWYgKCEgaXNfZGlyKF9fRElSX18uJy92ZW5kb3InKSkgewogICAgICAgICAgICBAc3ltbGluaygkd2ViVmVuZG9yRGlyLCBfX0RJUl9fLicvdmVuZG9yJyk7CiAgICAgICAgfQogICAgfQogICAgaWYgKCEgaXNfZmlsZSgkdmVuZG9yQXV0b2xvYWQpKSB7CiAgICAgICAgaHR0cF9yZXNwb25zZV9jb2RlKDUwMCk7CiAgICAgICAgaGVhZGVyKCdDb250ZW50LVR5cGU6IHRleHQvcGxhaW47IGNoYXJzZXQ9dXRmLTgnKTsKICAgICAgICBlY2hvICJMYXJhdmVsIHZlbmRvciBrbGFzb3J1IGJ1bHVuYW1hZGkuIENhbGlzdGlyOiBwaHAgbGFyYXZlbC12ZW5kb3ItZXh0cmFjdC5waHA/a2V5PWdrLWNwYW5lbC1zZXR1cC0yMDI2XG4iOwogICAgICAgIGV4aXQoMSk7CiAgICB9Cn0KCnJlcXVpcmUgJHZlbmRvckF1dG9sb2FkOwoKKHJlcXVpcmVfb25jZSBfX0RJUl9fLicvYm9vdHN0cmFwL2FwcC5waHAnKQogICAgLT5oYW5kbGVSZXF1ZXN0KFJlcXVlc3Q6OmNhcHR1cmUoKSk7Cg==", "bootstrap/app.php": "PD9waHAKCnVzZSBBcHBcSHR0cFxNaWRkbGV3YXJlXEFkbWluTWlkZGxld2FyZTsKdXNlIEFwcFxIdHRwXE1pZGRsZXdhcmVcQXBwbHlVc2VyTG9jYWxlOwp1c2UgQXBwXEh0dHBcTWlkZGxld2FyZVxDYXB0dXJlR3Jvd3RoQXR0cmlidXRpb247CnVzZSBBcHBcSHR0cFxNaWRkbGV3YXJlXFJlcXVpcmVTdXBlckFkbWluOwp1c2UgQXBwXEh0dHBcTWlkZGxld2FyZVxTZWN1cml0eUhlYWRlcnNNaWRkbGV3YXJlOwp1c2UgQXBwXEh0dHBcTWlkZGxld2FyZVxTZXRMb2NhbGU7CnVzZSBBcHBcSHR0cFxNaWRkbGV3YXJlXFNldHVwQWNjZXNzTWlkZGxld2FyZTsKdXNlIEFwcFxIdHRwXE1pZGRsZXdhcmVcVHJhY2tMYXN0QWN0aXZlOwp1c2UgQXBwXFN1cHBvcnRcQWRtaW5BcHA7CnVzZSBJbGx1bWluYXRlXEZvdW5kYXRpb25cQXBwbGljYXRpb247CnVzZSBJbGx1bWluYXRlXEZvdW5kYXRpb25cQ29uZmlndXJhdGlvblxFeGNlcHRpb25zOwp1c2UgSWxsdW1pbmF0ZVxGb3VuZGF0aW9uXENvbmZpZ3VyYXRpb25cTWlkZGxld2FyZTsKdXNlIElsbHVtaW5hdGVcSHR0cFxSZXF1ZXN0OwoKcmV0dXJuIEFwcGxpY2F0aW9uOjpjb25maWd1cmUoYmFzZVBhdGg6IGRpcm5hbWUoX19ESVJfXykpCiAgICAtPndpdGhQcm92aWRlcnMoKQogICAgLT53aXRoUm91dGluZygKICAgICAgICB3ZWI6IF9fRElSX18uJy8uLi9yb3V0ZXMvd2ViLnBocCcsCiAgICAgICAgYXBpOiBfX0RJUl9fLicvLi4vcm91dGVzL2FwaS5waHAnLAogICAgICAgIGNvbW1hbmRzOiBfX0RJUl9fLicvLi4vcm91dGVzL2NvbnNvbGUucGhwJywKICAgICAgICBjaGFubmVsczogX19ESVJfXy4nLy4uL3JvdXRlcy9jaGFubmVscy5waHAnLAogICAgICAgIGhlYWx0aDogJy91cCcsCiAgICApCiAgICAtPndpdGhNaWRkbGV3YXJlKGZ1bmN0aW9uIChNaWRkbGV3YXJlICRtaWRkbGV3YXJlKSB7CiAgICAgICAgJG1pZGRsZXdhcmUtPmFsaWFzKFsKICAgICAgICAgICAgJ2FkbWluJyA9PiBBZG1pbk1pZGRsZXdhcmU6OmNsYXNzLAogICAgICAgICAgICAnYWRtaW4uc3VwZXInID0+IFJlcXVpcmVTdXBlckFkbWluOjpjbGFzcywKICAgICAgICAgICAgJ2xvY2FsZScgPT4gQXBwbHlVc2VyTG9jYWxlOjpjbGFzcywKICAgICAgICAgICAgJ3NldHVwJyA9PiBTZXR1cEFjY2Vzc01pZGRsZXdhcmU6OmNsYXNzLAogICAgICAgIF0pOwoKICAgICAgICAkbWlkZGxld2FyZS0+ZW5jcnlwdENvb2tpZXMoZXhjZXB0OiBbCiAgICAgICAgICAgICdna19sb2NhbGUnLAogICAgICAgIF0pOwoKICAgICAgICAkbWlkZGxld2FyZS0+YXBwZW5kVG9Hcm91cCgnd2ViJywgWwogICAgICAgICAgICBTZWN1cml0eUhlYWRlcnNNaWRkbGV3YXJlOjpjbGFzcywKICAgICAgICAgICAgQ2FwdHVyZUdyb3d0aEF0dHJpYnV0aW9uOjpjbGFzcywKICAgICAgICBdKTsKCiAgICAgICAgaWYgKGNsYXNzX2V4aXN0cyhTZXRMb2NhbGU6OmNsYXNzKSkgewogICAgICAgICAgICAkbWlkZGxld2FyZS0+YXBwZW5kVG9Hcm91cCgnd2ViJywgW1NldExvY2FsZTo6Y2xhc3NdKTsKICAgICAgICB9CgogICAgICAgIGlmIChjbGFzc19leGlzdHMoVHJhY2tMYXN0QWN0aXZlOjpjbGFzcykpIHsKICAgICAgICAgICAgJG1pZGRsZXdhcmUtPmFwcGVuZFRvR3JvdXAoJ3dlYicsIFtUcmFja0xhc3RBY3RpdmU6OmNsYXNzXSk7CiAgICAgICAgICAgICRtaWRkbGV3YXJlLT5hcHBlbmRUb0dyb3VwKCdhcGknLCBbVHJhY2tMYXN0QWN0aXZlOjpjbGFzc10pOwogICAgICAgIH0KCiAgICAgICAgJG1pZGRsZXdhcmUtPnJlZGlyZWN0R3Vlc3RzVG8oZnVuY3Rpb24gKFJlcXVlc3QgJHJlcXVlc3QpIHsKICAgICAgICAgICAgaWYgKEFkbWluQXBwOjppc1N1YmRvbWFpblJlcXVlc3QoKSkgewogICAgICAgICAgICAgICAgcmV0dXJuIEFkbWluQXBwOjpsb2dpblBhdGgoKTsKICAgICAgICAgICAgfQoKICAgICAgICAgICAgaWYgKHN0cl9zdGFydHNfd2l0aCh0cmltKCRyZXF1ZXN0LT5wYXRoKCksICcvJyksICdhZG1pbmxvZ2luJykpIHsKICAgICAgICAgICAgICAgIHJldHVybiAnaHR0cHM6Ly9hZG1pbi5nb251bGtvcHJ1c3UuY29tL2xvZ2luJzsKICAgICAgICAgICAgfQoKICAgICAgICAgICAgcmV0dXJuIHJvdXRlKCdsb2dpbicpOwogICAgICAgIH0pOwogICAgfSkKICAgIC0+d2l0aEV4Y2VwdGlvbnMoZnVuY3Rpb24gKEV4Y2VwdGlvbnMgJGV4Y2VwdGlvbnMpIHsKICAgICAgICAvLwogICAgfSktPmNyZWF0ZSgpOwoK", "artisan": "IyEvdXNyL2Jpbi9lbnYgcGhwCjw/cGhwCgpkZWZpbmUoJ0xBUkFWRUxfU1RBUlQnLCBtaWNyb3RpbWUodHJ1ZSkpOwoKcmVxdWlyZSBfX0RJUl9fLicvdmVuZG9yL2F1dG9sb2FkLnBocCc7CgokYXBwID0gcmVxdWlyZV9vbmNlIF9fRElSX18uJy9ib290c3RyYXAvYXBwLnBocCc7Cgoka2VybmVsID0gJGFwcC0+bWFrZShJbGx1bWluYXRlXENvbnRyYWN0c1xDb25zb2xlXEtlcm5lbDo6Y2xhc3MpOwokc3RhdHVzID0gJGtlcm5lbC0+aGFuZGxlKAogICAgJGlucHV0ID0gbmV3IFN5bWZvbnlcQ29tcG9uZW50XENvbnNvbGVcSW5wdXRcQXJndklucHV0LAogICAgbmV3IFN5bWZvbnlcQ29tcG9uZW50XENvbnNvbGVcT3V0cHV0XENvbnNvbGVPdXRwdXQKKTsKJGtlcm5lbC0+dGVybWluYXRlKCRpbnB1dCwgJHN0YXR1cyk7CmV4aXQoJHN0YXR1cyk7Cg==", ".env": "QVBQX05BTUU9IkfDtm7DvGwgS8O2cHLDvHPDvCBBZG1pbiIKQVBQX0VOVj1wcm9kdWN0aW9uCkFQUF9LRVk9YmFzZTY0OmJZWFVEcDh1SHlxSXJRWFhqSi9pN0hlRkRtUU43RjU4UTJqdnUrN2pmOE09CkFQUF9ERUJVRz1mYWxzZQpBUFBfVVJMPWh0dHBzOi8vYWRtaW4uZ29udWxrb3BydXN1LmNvbQpBRE1JTl9VUkw9aHR0cHM6Ly9hZG1pbi5nb251bGtvcHJ1c3UuY29tCkFTU0VUX1VSTD1odHRwczovL2FkbWluLmdvbnVsa29wcnVzdS5jb20KCkxPR19DSEFOTkVMPXN0YWNrCkxPR19MRVZFTD1lcnJvcgoKREJfQ09OTkVDVElPTj1teXNxbApEQl9IT1NUPWxvY2FsaG9zdApEQl9QT1JUPTMzMDYKREJfREFUQUJBU0U9Z29udWxrb3Bfd2VwYXBwCkRCX1VTRVJOQU1FPWdvbnVsa29wX2FkbWluCkRCX1BBU1NXT1JEPU1obXQ0OTgwNzEKCkJST0FEQ0FTVF9DT05ORUNUSU9OPWxvZwpDQUNIRV9TVE9SRT1maWxlCkZJTEVTWVNURU1fRElTSz1sb2NhbApRVUVVRV9DT05ORUNUSU9OPXN5bmMKU0VTU0lPTl9EUklWRVI9ZmlsZQpTRVNTSU9OX0xJRkVUSU1FPTEyMApTRVNTSU9OX0RPTUFJTj0KU0VTU0lPTl9TRUNVUkVfQ09PS0lFPXRydWUKU0FOQ1RVTV9TVEFURUZVTF9ET01BSU5TPWdvbnVsa29wcnVzdS5jb20sd3d3LmdvbnVsa29wcnVzdS5jb20sYWRtaW4uZ29udWxrb3BydXN1LmNvbQoKRlRQX0hPU1Q9ZnRwLmdvbnVsa29wcnVzdS5jb20KRlRQX1VTRVJOQU1FPXdlYkBnb251bGtvcHJ1c3UuY29tCkZUUF9QQVNTV09SRD1NaG10NDk4MDcxCkZUUF9QT1JUPTIxCkZUUF9TU0w9ZmFsc2UKRlRQX1BBU1NJVkU9dHJ1ZQpGVFBfTUVESUFfUk9PVD0vaG9tZS9nb251bGtvcC9wdWJsaWNfaHRtbC91cGxvYWRzCkZUUF9ST09UPS9ob21lL2dvbnVsa29wL3B1YmxpY19odG1sCgpNRURJQV9ESVNLPW1lZGlhX2xvY2FsCk1FRElBX0xPQ0FMX1JPT1Q9L2hvbWUvZ29udWxrb3AvcHVibGljX2h0bWwvdXBsb2FkcwpNRURJQV9VUkw9aHR0cHM6Ly93d3cuZ29udWxrb3BydXN1LmNvbS91cGxvYWRzCkNETl9VUkw9aHR0cHM6Ly93d3cuZ29udWxrb3BydXN1LmNvbS91cGxvYWRzCgpNQUlMX01BSUxFUj1zbXRwCk1BSUxfSE9TVD1tdC1sdWNhLmd1emVsaG9zdGluZy5jb20KTUFJTF9QT1JUPTQ2NQpNQUlMX1VTRVJOQU1FPWRlc3Rla0Bnb251bGtvcHJ1c3UuY29tCk1BSUxfUEFTU1dPUkQ9TWhtdDQ5ODA3MQpNQUlMX0VOQ1JZUFRJT049c3NsCk1BSUxfRlJPTV9BRERSRVNTPWRlc3Rla0Bnb251bGtvcHJ1c3UuY29tCk1BSUxfRlJPTV9OQU1FPSJHw7Zuw7xsIEvDtnByw7xzw7wiCgpQUkVNSVVNX1BST19QUklDRT0yNTAKUFJFTUlVTV9HT0xEX1BSSUNFPTMwMApQUkVNSVVNX1BMQVRJTlVNX1BSSUNFPTUwMAoKR09PR0xFX0NMSUVOVF9JRD0xMzkyODg1MjUyMzctZGRuNGt0MzN0ajVyY3I3OGJsZW9lYzVrZTZlM3A0bm4uYXBwcy5nb29nbGV1c2VyY29udGVudC5jb20KR09PR0xFX0NMSUVOVF9TRUNSRVQ9R09DU1BYLVdfaGxnTnI0SldBM0hER2Rta2lOUUZydVUwUzMKR09PR0xFX1JFRElSRUNUX1VSST1odHRwczovL3d3dy5nb251bGtvcHJ1c3UuY29tL2F1dGgvZ29vZ2xlL2NhbGxiYWNrCkdPT0dMRV9BTkRST0lEX0NMSUVOVF9JRD0xMzkyODg1MjUyMzctMnNjanBlcDhhNGFrZDkzMTIyZzkwanRyczE3Yms1M3MuYXBwcy5nb29nbGV1c2VyY29udGVudC5jb20KCgpBRE1JTl9TVUJET01BSU49ZmFsc2UKCiMgQWRtaW4gUGFuZWwgU3BlY2lmaWMKQURNSU5fU1VCRE9NQUlOPXRydWUKU0VUVVBfQ0FDSEVfS0VZPWdrLWNwYW5lbC1zZXR1cC0yMDI2ClNFU1NJT05fQ09PS0lFPWdvbnVsX2tvcHJ1c3VfYWRtaW5fc2Vzc2lvbgpTRVNTSU9OX0RPTUFJTj0KU0VTU0lPTl9TRUNVUkVfQ09PS0lFPXRydWUKU0VTU0lPTl9IVFRQX09OTFk9dHJ1ZQpTRVNTSU9OX1NBTUVfU0lURT1sYXgKCiMgRGVwbG95CkRFUExPWV9HSVRIVUJfUkVQTz1odHRwczovL2dpdGh1Yi5jb20vbWVobWV0NDk4MDcvZGVubWUKREVQTE9ZX0dJVEhVQl9CUkFOQ0g9bWFzdGVyCkRFUExPWV9HSVRIVUJfQUNUSU9OU19VUkw9aHR0cHM6Ly9naXRodWIuY29tL21laG1ldDQ5ODA3L2Rlbm1lL2FjdGlvbnMvd29ya2Zsb3dzL2RlcGxveS55bWwKREVQTE9ZX0dJVEhVQl9DT01QQVJFX1VSTD1odHRwczovL2dpdGh1Yi5jb20vbWVobWV0NDk4MDcvZGVubWUvY29tcGFyZS9tYXN0ZXIuLi5tYXN0ZXIKREVQTE9ZX0dJVEhVQl9TRUNSRVRTX1VSTD1odHRwczovL2dpdGh1Yi5jb20vbWVobWV0NDk4MDcvZGVubWUvc2V0dGluZ3Mvc2VjcmV0cy9hY3Rpb25zCkRFUExPWV9HSVRIVUJfUkVQT19TTFVHPW1laG1ldDQ5ODA3L2Rlbm1lCkRFUExPWV9HSVRIVUJfV09SS0ZMT1c9ZGVwbG95LnltbApERVBMT1lfQUxFUlRfRU1BSUw9ZGVzdGVrQGdvbnVsa29wcnVzdS5jb20KREVQTE9ZX1dFQl9VUkw9aHR0cHM6Ly9nb251bGtvcHJ1c3UuY29tCkRFUExPWV9BRE1JTl9VUkw9aHR0cHM6Ly9hZG1pbi5nb251bGtvcHJ1c3UuY29tCgojIEZpcmViYXNlCkZJUkVCQVNFX1BST0pFQ1RfSUQ9Z29udWxrb3BydXN1LTMyNWViCg=="}
JSON, true);

foreach ($files as $rel => $b64) {
    $path = $adminRoot.'/'.$rel;
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel ".filesize($path)."\n";
}

// Create storage directories
$dirs = [
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/app/public',
    'storage/logs',
    'bootstrap/cache',
];

foreach ($dirs as $dir) {
    $path = $adminRoot.'/'.$dir;
    if (! is_dir($path)) {
        @mkdir($path, 0755, true);
        echo "mkdir $dir\n";
    }
}

// Try to symlink vendor from web-site
$vendorDir = $adminRoot.'/vendor';
if (! is_dir($vendorDir)) {
    $webRoots = [
        dirname($adminRoot).'/gonulkoprusu.com',
        dirname($adminRoot).'/public_html',
        '/home/gonulkop/gonulkoprusu.com',
        '/home/gonulkop/public_html',
    ];
    $linked = false;
    foreach ($webRoots as $webRoot) {
        if (is_dir($webRoot.'/vendor/laravel/framework')) {
            @symlink($webRoot.'/vendor', $vendorDir);
            if (is_file($vendorDir.'/autoload.php')) {
                echo "vendor symlinked from $webRoot\n";
                $linked = true;
                break;
            }
        }
    }
    if (! $linked) {
        // Try copying vendor
        foreach ($webRoots as $webRoot) {
            if (is_dir($webRoot.'/vendor/laravel/framework')) {
                echo "attempting vendor copy from $webRoot ...\n";
                $cmd = 'cp -r '.escapeshellarg($webRoot.'/vendor').' '.escapeshellarg($vendorDir).' 2>&1';
                $output = @shell_exec($cmd);
                if (is_file($vendorDir.'/autoload.php')) {
                    echo "vendor copied from $webRoot\n";
                    $linked = true;
                    break;
                }
                echo "copy failed: ".substr($output ?? '', 0, 200)."\n";
            }
        }
    }
    if (! $linked && is_file($adminRoot.'/laravel-vendor-extract.php')) {
        echo "vendor not found - run laravel-vendor-extract.php manually\n";
    }
}

// Clear all cache
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan cache:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan config:clear 2>/dev/null');

if (function_exists('opcache_reset')) {
    @opcache_reset();
}

echo "OK\n";
