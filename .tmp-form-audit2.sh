#!/bin/bash
set -e
for url in /dev/overview /dev/site /dev/theme /dev/pages /dev/pages/new /dev/pages/_ /dev/login; do
  echo "=== $url ==="
  curl -s "http://localhost:8080$url" > /tmp/audit-page.html
  python3 -c "
import re
html = open('/tmp/audit-page.html').read()
html = re.sub(r'<!--.*?-->', '', html, flags=re.S)
depth = 0
issues = 0
for m in re.finditer(r'<(/?)form\b[^>]*>', html):
    is_close = m.group(1) == '/'
    if is_close:
        depth -= 1
    else:
        depth += 1
        if depth > 1:
            issues += 1
            line = html[:m.start()].count(chr(10)) + 1
            print('  NESTED at line', line, m.group(0)[:110])
print('  issues:', issues, '| final depth:', depth)
"
done
