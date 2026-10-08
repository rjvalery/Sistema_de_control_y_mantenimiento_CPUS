import os
import glob

def fix_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()
        
    original = content
    content = content.replace("import React, { ", "import { ")
    content = content.replace("import React from 'react'\n", "")
    content = content.replace("import { createFileRoute, Link, Outlet, redirect }", "import { createFileRoute, Link, Outlet }")
    content = content.replace("<Link to={`/inventario/${item.id}`}", '<Link to="/inventario/$id" params={{ id: str(item.id) }}') # Fix TS
    content = content.replace('<Link to="/inventario/$id" params={{ id: str(item.id) }}', '<Link to="/inventario/$id" params={{ id: String(item.id) }}') # Wait JS doesn't have str()
    
    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Fixed {filepath}")

for filepath in glob.glob("src/**/*.tsx", recursive=True):
    fix_file(filepath)
