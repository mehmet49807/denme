#!/usr/bin/env python3
from ftplib import FTP
from io import BytesIO
import pathlib
import sys

text = pathlib.Path('/tmp/live-web.php').read_text()
text = text.replace(
    "if ($gkHttpHost === 'gonulkoprusu.com') {",
    "if ($gkHttpHost === 'www.gonulkoprusu.com') {",
)
text = text.replace(
    "header('Location: https://www.gonulkoprusu.com'",
    "header('Location: https://gonulkoprusu.com'",
)
text = text.replace('# https://www.gonulkoprusu.com', '# https://gonulkoprusu.com')
text = text.replace(
    'Sitemap: https://www.gonulkoprusu.com/sitemap.xml',
    'Sitemap: https://gonulkoprusu.com/sitemap.xml',
)
data = text.encode()
print('uploading', len(data), flush=True)
ftp = FTP('ftp.gonulkoprusu.com', timeout=300)
ftp.login('web@gonulkoprusu.com', 'Mhmt498071')
ftp.set_pasv(True)
ftp.storbinary('STOR routes/web.php', BytesIO(data), blocksize=32768)
sz = ftp.size('routes/web.php')
ftp.quit()
print('result', sz, flush=True)
if sz != len(data):
    sys.exit(1)
