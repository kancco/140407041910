from datetime import datetime

def append_chat_to_md(chat_file, md_file):
    with open(chat_file, 'r', encoding='utf-8') as cfile:
        chat_text = cfile.read()
    with open(md_file, 'a', encoding='utf-8') as mfile:
        mfile.write('\n\n---\n\n')
        mfile.write(f'### گفتگو ثبت شده در تاریخ {datetime.now().strftime("%Y-%m-%d %H:%M")}\n')
        mfile.write(chat_text)
    print("گفتگو به فایل مستندات اضافه شد.")

# مسیر فایل‌ها را تنظیم کن
append_chat_to_md('chat.txt', 'PROJECT-DESIGN.md')