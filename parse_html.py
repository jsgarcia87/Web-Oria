import os
import re

html_files = [f for f in os.listdir('.') if f.endswith('.html') and f not in ['admin.html']]
all_contents = []

for file in html_files:
    if file == 'admin.html': continue
    
    with open(file, 'r', encoding='utf-8') as f:
        html = f.read()
        
    page_name = file.replace('.html', '')
    counter = 1
    
    # Regex to find <h1...>, <h2...>, <p...> tags and their contents
    # This regex looks for <tag...>(content)</tag>
    # It tries to avoid matching tags that already have data-content-id
    pattern = re.compile(r'(<(h[1-6]|p)(?![^>]*data-content-id)[^>]*>)(.*?)(</\2>)', re.IGNORECASE | re.DOTALL)
    
    def replacer(match):
        global counter
        opening_tag = match.group(1)
        tag_name = match.group(2)
        inner_html = match.group(3)
        closing_tag = match.group(4)
        
        # Skip empty ones
        if not inner_html.strip():
            return match.group(0)
            
        content_id = f"{page_name}_{tag_name.lower()}_{counter}"
        counter += 1
        
        all_contents.append({
            'id': content_id,
            'page': page_name,
            'content_value': inner_html.strip()
        })
        
        # Insert data-content-id before the closing > of the opening tag
        # Handle self-closing slash just in case, though p/h shouldn't have it
        if opening_tag.endswith('/>'):
            new_opening = opening_tag[:-2] + f' data-content-id="{content_id}"/>'
        else:
            new_opening = opening_tag[:-1] + f' data-content-id="{content_id}">'
            
        return new_opening + inner_html + closing_tag

    new_html = pattern.sub(replacer, html)
    
    with open(file, 'w', encoding='utf-8') as f:
        f.write(new_html)

# Now generate PHP
php_inserts = ""
for item in all_contents:
    cid = item['id']
    cval = item['content_value'].replace("'", "\\'") 
    cpage = item['page']
    
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

success_marker = 'echo "<b>¡Instalación completada!</b>'
if success_marker in setup_php:
    new_setup = setup_php.replace(success_marker, "    // Contenidos extraídos automáticamente\\n" + php_inserts + "\\n    " + success_marker)
    with open('api/setup.php', 'w', encoding='utf-8') as f:
        f.write(new_setup)
else:
    print("Could not find marker in setup.php")

print(f"Processed {len(all_contents)} tags across {len(html_files)} files.")
