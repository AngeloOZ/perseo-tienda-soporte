import subprocess
import sys

def convert_doc_to_pdf(input_file, output_file):
    cmd = f"libreoffice --headless --convert-to pdf:writer_pdf_Export --outdir {output_file} {input_file}"

    try:
        result = subprocess.run(cmd, shell=True, check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        return result.stdout.decode(), None
    except subprocess.CalledProcessError as e:
        return None, e.stderr.decode()

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Uso: script.py [input_file] [output_folder]")
        sys.exit(1)

    input_file = sys.argv[1]
    output_folder = sys.argv[2]

    success, error = convert_doc_to_pdf(input_file, output_folder)

    if error:
        print(f"Error: {error}")
    else:
        print(f"Success: {success}")
