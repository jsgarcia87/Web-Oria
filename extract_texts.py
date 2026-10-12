import os
import json
from html.parser import HTMLParser

# We will use standard python libraries if possible, or try to import bs4.
try:
    from bs4 import BeautifulSoup
except ImportError:
    os.system("pip3 install beautifulsoup4 --user")
    from bs4 import BeautifulSoup

html_files = [f for f in os.listdir('.') if f.endswith('.html') and f not in ['admin.html']]

all_contents = []

for file in html_files:
    if file == 'admin.html': continue
    
    with open(file, 'r', encoding='utf-8') as f:
        html = f.read()
        
    soup = BeautifulSoup(html, 'html.parser')
    page_name = file.replace('.html', '')
    
    # We will look for headings and paragraphs
    tags_to_extract = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p']
    counter = 1
    
    for tag in soup.find_all(tags_to_extract):
        # Skip if it's empty or already has data-content-id
        if not tag.text.strip():
            continue
        if tag.has_attr('data-content-id'):
            continue
            
        # Give it an ID
        content_id = f"{page_name}_{tag.name}_{counter}"
        
        # Save original content (inner html)
        inner_html = "".join([str(c) for c in tag.contents]).strip()
        
        # Add attribute
        tag['data-content-id'] = content_id
        
        # Append to our list
        all_contents.append({
            'id': content_id,
            'page': page_name,
            'content_value': inner_html
        })
        counter += 1
        
    # Write the modified HTML back
    with open(file, 'w', encoding='utf-8') as f:
        f.write(str(soup))

# Now generate the PHP code to append to setup.php
php_inserts = ""
for item in all_contents:
    cid = item['id']
    cval = item['content_value'].replace("'", "\\'") # Escape single quotes for PHP
    cpage = item['page']
    
    # PHP PDO snippet
    php_inserts += f"""
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = '{cid}'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {{
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('{cid}', ?, '{cpage}')");
        $insert->execute(['{cval}']);
    }}
"""

with open('api/setup.php', 'r', encoding='utf-8') as f:
    setup_php = f.read()

# Insert before the success message
success_marker = 'echo "<b>¡Instalación completada!</b>'
if success_marker in setup_php:
    new_setup = setup_php.replace(success_marker, "    // Contenidos extraidos automáticamente\n" + php_inserts + "\n    " + success_marker)
    with open('api/setup.php', 'w', encoding='utf-8') as f:
        f.write(new_setup)
else:
    print("Could not find the marker in setup.php")

print(f"Processed {len(all_contents)} text blocks across {len(html_files)} files.")
