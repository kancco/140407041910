export async function fetchWhitelist(query = "") {
  let url = "/api/whitelist.php";
  if (query) url += "?q=" + encodeURIComponent(query);
  let res = await fetch(url);
  let list;
  try {
    list = await res.json();
  } catch(e) {
    document.getElementById("wl-results").innerHTML = "<div style='color:red'>خطا در دریافت اطلاعات یا خروجی JSON ندارد</div>";
    return;
  }
  if(!Array.isArray(list) || list.length === 0){
    document.getElementById("wl-results").innerHTML = "<div style='color:red'>داده‌ای یافت نشد</div>";
    return;
  }
  let html = `<div class="table-responsive" style="margin-top:18px;">
    <table class="modern-table wl-table">
      <thead>
        <tr>
          <th>شناسه ملی</th>
          <th>نام سایت تولیدی</th>
          <th>نوع صنعت تولیدی</th>
          <th>نوع سایت تولیدی</th>
          <th>نام مدیر</th>
        </tr>
      </thead>
      <tbody>`;
  for (const company of list) {
    html += `<tr>
      <td>${company.national_id || "-"}</td>
      <td>${company.factory_name_fa || "-"}</td>
      <td>${company.manufacturing_industry_type || "-"}</td>
      <td>${company.production_site_type || "-"}</td>
      <td>${company.manager_name_fa || "-"}</td>
    </tr>`;
  }
  html += `</tbody></table></div>`;
  document.getElementById("wl-results").innerHTML = html;
}

// اجرای اولیه و اتصال به سرچ‌باکس
document.getElementById("wl-search").onclick = () => {
  fetchWhitelist(document.getElementById("wl-q").value);
};
document.getElementById("wl-q").addEventListener("keydown", e => {
  if (e.key === "Enter") fetchWhitelist(e.target.value);
});
fetchWhitelist();