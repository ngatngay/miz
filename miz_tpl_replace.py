import re
import sys

def read_file(path):
    with open(path, 'r', encoding='utf-8') as f:
        return f.read().expandtabs(4)

def write_file(path, content):
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

BLOCK_REGEX_TEMPLATE = r'#(?P<name>\w+)_start.*?#(?P=name)_end'
BLOCK_PATTERN = re.compile(BLOCK_REGEX_TEMPLATE, re.DOTALL)

def extract_blocks(text):
    return {m.group('name'): m.group(0) for m in BLOCK_PATTERN.finditer(text)}

def make_block_pattern(name):
    return re.compile(rf'#({name})_start(.*?)#\1_end', re.DOTALL)

def replace_blocks(original, replacements):
    for name, new_block in replacements.items():
        pattern = make_block_pattern(name)
        original, count = pattern.subn(new_block, original)
        #if count > 0:
            #print(f"> Replaced block: {name}")
    return original

def main():
    if len(sys.argv) != 4:
        print("Usage: python3 parse.py tpl input dest")
        sys.exit(1)

    tpl_path, input_path, dest_path = sys.argv[1], sys.argv[2], sys.argv[3]

    tpl_content = read_file(tpl_path)
    input_content = read_file(input_path)

    blocks_input = extract_blocks(input_content)
    result = replace_blocks(tpl_content, blocks_input)
    write_file(dest_path, result)

    #print(f"Done. Output saved to: {dest_path}")

if __name__ == '__main__':
    main()