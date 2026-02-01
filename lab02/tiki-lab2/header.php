<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($pageTitle ?? "Trang sản phẩm"); ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; background: #f5f5fa; }
    .topbar { background: #1a94ff; color: white; padding: 12px 16px; }
    .topbar .brand { font-weight: 900; font-size: 20px; }
    .container { max-width: 1100px; margin: 16px auto; padding: 0 12px; }

    .layout { display: grid; grid-template-columns: 260px 1fr; gap: 16px; }
    .panel { background: white; border-radius: 12px; padding: 12px; }

    .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    .card { background: white; border-radius: 12px; padding: 12px; border: 1px solid #eee; }
    .thumb { width: 100%; height: 140px; object-fit: contain; background: #fafafa; border-radius: 10px; }
    .name { margin: 10px 0 6px; font-weight: 700; font-size: 13px; min-height: 36px; }
    .price { font-weight: 900; }
    .old { color: #888; text-decoration: line-through; margin-left: 6px; font-size: 12px; }
    .discount { color: #d0021b; font-weight: 900; margin-left: 6px; font-size: 12px; }
    .rating { color: #f5a623; font-size: 12px; }
    .muted { color: #666; font-size: 13px; }

    @media (max-width: 980px) { .grid { grid-template-columns: repeat(3, 1fr);} }
    @media (max-width: 800px) { .layout { grid-template-columns: 1fr; } }
    @media (max-width: 600px) { .grid { grid-template-columns: repeat(2, 1fr);} }
  </style>
</head>
<body>
  <div class="topbar">
    <div class="brand">tiki (clone)</div>
  </div>
  <div class="container">
