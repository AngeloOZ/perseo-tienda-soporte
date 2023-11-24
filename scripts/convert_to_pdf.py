import subprocess

def convert_doc_to_pdf(input_file, output_file):
    cmd = f"libreoffice --headless --convert-to pdf:writer_pdf_Export --outdir {output_file} {input_file}"
    subprocess.run(cmd, shell=True)

convert_doc_to_pdf("input.docx", "/path/to/output/folder")
