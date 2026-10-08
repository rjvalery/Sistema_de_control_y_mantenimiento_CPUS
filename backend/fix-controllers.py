import os
import glob
import re

def fix_controller(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    original = content
    
    # Replace return view('...', $data); with return response()->json($data);
    # Regex to catch return view('...', compact('...')); or return view('...', $vars);
    # It's tricky because $datos might be an array or variable.
    
    # 1. return view('something', $var); -> return response()->json($var);
    content = re.sub(r"return\s+view\([^,]+,\s*(.+?)\);", r"return response()->json(\1);", content)
    
    # 2. return view('something'); -> return response()->json(['success' => true]);
    content = re.sub(r"return\s+view\([^,]+\);", r"return response()->json(['success' => true]);", content)
    
    # 3. redirect()->route(...)->with('error', '...') -> response()->json(['error' => '...'], 403)
    content = re.sub(r"return\s+redirect\(\)->route\([^)]+\)->with\('error',\s*(.+?)\);", r"return response()->json(['error' => \1], 403);", content)
    content = re.sub(r"return\s+back\(\)->withInput\(\)->with\('error',\s*(.+?)\);", r"return response()->json(['error' => \1], 500);", content)
    
    # 4. redirect()->route(...)->with('msg', '...') -> response()->json(['message' => '...'])
    content = re.sub(r"return\s+redirect\(\)->route\([^)]+\)->with\('msg',\s*(.+?)\);", r"return response()->json(['message' => \1]);", content)

    # 5. if ($request->ajax() || $request->wantsJson()) { ... } return redirect... 
    # Just remove the if block and let it return json directly.
    content = re.sub(r"if\s*\(\$request->ajax\(\)\s*\|\|\s*\$request->wantsJson\(\)\)\s*\{\s*return\s*response\(\)->json\((.*?)\);\s*\}\s*return\s*redirect[^\;]+\;", r"return response()->json(\1);", content, flags=re.DOTALL)
    content = re.sub(r"if\s*\(\$request->ajax\(\)\s*\|\|\s*\$request->wantsJson\(\)\)\s*\{\s*return\s*response\(\)->json\((.*?)\,\s*(\d+)\);\s*\}\s*return\s*back[^\;]+\;", r"return response()->json(\1, \2);", content, flags=re.DOTALL)


    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Fixed {filepath}")

for filepath in glob.glob("app/Http/Controllers/**/*.php", recursive=True):
    fix_controller(filepath)
