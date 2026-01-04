# Download Premium Product Images for Zambezi Meats
# Uses Unsplash API for high-quality, free-to-use images

$baseDir = "c:\xampp\htdocs\Zambezi-Meats\frontend\public\images\products"

# Product image mappings - Unsplash photo IDs for premium meat products
$images = @{
    # BEEF PRODUCTS
    "beef/rump-steak.jpg" = "https://images.unsplash.com/photo-1588168333986-5078d3ae3976?w=800&q=80" # Rump steak
    "beef/t-bone-steak.jpg" = "https://images.unsplash.com/photo-1603360946369-dc9bb6258143?w=800&q=80" # T-bone
    "beef/new-york-cut-steak.jpg" = "https://images.unsplash.com/photo-1546833998-877b37c2e5c6?w=800&q=80" # NY strip
    "beef/scotch-fillet.jpg" = "https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80" # Ribeye/Scotch fillet
    "beef/eye-fillet-steak.jpg" = "https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80" # Tenderloin
    "beef/x-cut-blade.jpg" = "https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=800&q=80" # Blade steak
    "beef/round-steak.jpg" = "https://images.unsplash.com/photo-1602470520998-f4a52199a3d6?w=800&q=80" # Round steak
    "beef/topside-steak.jpg" = "https://images.unsplash.com/photo-1553163147-622ab57be5fd?w=800&q=80" # Topside
    "beef/roast-silverside.jpg" = "https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=800&q=80" # Beef roast
    "beef/corned-silverside.jpg" = "https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=800&q=80" # Corned beef
    "beef/diced-gravy-beef.jpg" = "https://images.unsplash.com/photo-1501200291289-c5a76c232e5f?w=800&q=80" # Diced beef
    "beef/lean-diced-chuck.jpg" = "https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=800&q=80" # Diced chuck
    "beef/lean-diced-beef.jpg" = "https://images.unsplash.com/photo-1603360946369-dc9bb6258143?w=800&q=80" # Lean diced
    "beef/beef-mince.jpg" = "https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=800&q=80" # Beef mince
    "beef/premium-beef-mince.jpg" = "https://images.unsplash.com/photo-1603360946369-dc9bb6258143?w=800&q=80" # Premium mince
    "beef/lean-beef-mince.jpg" = "https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=800&q=80" # Lean mince
    "beef/beef-ribs.jpg" = "https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80" # Beef ribs
    "beef/beef-short-ribs.jpg" = "https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80" # Short ribs
    "beef/osso-bucco.jpg" = "https://images.unsplash.com/photo-1603360946369-dc9bb6258143?w=800&q=80" # Osso bucco
    "beef/oxtail.jpg" = "https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80" # Oxtail
    "beef/beef-bones.jpg" = "https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=800&q=80" # Beef bones
    "beef/beef-brisket.jpg" = "https://images.unsplash.com/photo-1602470520998-f4a52199a3d6?w=800&q=80" # Brisket
    
    # LAMB PRODUCTS
    "lamb/leg-of-lamb.jpg" = "https://images.unsplash.com/photo-1574781330693-d062ceabe0ce?w=800&q=80" # Leg of lamb
    "lamb/lamb-leg-chops.jpg" = "https://images.unsplash.com/photo-1535473895227-bdecb20fb157?w=800&q=80" # Lamb chops
    "lamb/lamb-chump-chops.jpg" = "https://images.unsplash.com/photo-1606851094291-6f42ecf461df?w=800&q=80" # Chump chops
    "lamb/lamb-loin-chops.jpg" = "https://images.unsplash.com/photo-1535473895227-bdecb20fb157?w=800&q=80" # Loin chops
    "lamb/lamb-forequarter-chops.jpg" = "https://images.unsplash.com/photo-1606851094291-6f42ecf461df?w=800&q=80" # Forequarter
    "lamb/lamb-cutlets.jpg" = "https://images.unsplash.com/photo-1574781330693-d062ceabe0ce?w=800&q=80" # Cutlets
    "lamb/lamb-rack.jpg" = "https://images.unsplash.com/photo-1535473895227-bdecb20fb157?w=800&q=80" # Rack of lamb
    "lamb/lamb-shoulder.jpg" = "https://images.unsplash.com/photo-1606851094291-6f42ecf461df?w=800&q=80" # Shoulder
    "lamb/lamb-shanks.jpg" = "https://images.unsplash.com/photo-1574781330693-d062ceabe0ce?w=800&q=80" # Shanks
    "lamb/lamb-neck.jpg" = "https://images.unsplash.com/photo-1535473895227-bdecb20fb157?w=800&q=80" # Lamb neck
    "lamb/diced-lamb.jpg" = "https://images.unsplash.com/photo-1606851094291-6f42ecf461df?w=800&q=80" # Diced lamb
    "lamb/lamb-mince.jpg" = "https://images.unsplash.com/photo-1574781330693-d062ceabe0ce?w=800&q=80" # Lamb mince
    "lamb/lamb-backstrap.jpg" = "https://images.unsplash.com/photo-1535473895227-bdecb20fb157?w=800&q=80" # Backstrap
    "lamb/butterflied-leg.jpg" = "https://images.unsplash.com/photo-1606851094291-6f42ecf461df?w=800&q=80" # Butterflied
    
    # PORK PRODUCTS
    "pork/pork-loin-chops.jpg" = "https://images.unsplash.com/photo-1546942113-a6c43b63104a?w=800&q=80" # Pork chops
    "pork/pork-cutlets.jpg" = "https://images.unsplash.com/photo-1602470520998-f4a52199a3d6?w=800&q=80" # Cutlets
    "pork/pork-spare-ribs.jpg" = "https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80" # Spare ribs
    "pork/pork-belly.jpg" = "https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80" # Pork belly
    "pork/pork-shoulder.jpg" = "https://images.unsplash.com/photo-1546942113-a6c43b63104a?w=800&q=80" # Shoulder
    "pork/pork-leg-roast.jpg" = "https://images.unsplash.com/photo-1602470520998-f4a52199a3d6?w=800&q=80" # Leg roast
    "pork/pork-fillet.jpg" = "https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80" # Fillet
    "pork/pork-mince.jpg" = "https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80" # Pork mince
    "pork/pork-sausage-meat.jpg" = "https://images.unsplash.com/photo-1546942113-a6c43b63104a?w=800&q=80" # Sausage meat
    "pork/diced-pork.jpg" = "https://images.unsplash.com/photo-1602470520998-f4a52199a3d6?w=800&q=80" # Diced pork
    "pork/bacon-rashers.jpg" = "https://images.unsplash.com/photo-1542574621-e088a4464f7e?w=800&q=80" # Bacon
    "pork/bacon-middle-rashers.jpg" = "https://images.unsplash.com/photo-1542574621-e088a4464f7e?w=800&q=80" # Middle bacon
    "pork/bacon-shortcut.jpg" = "https://images.unsplash.com/photo-1542574621-e088a4464f7e?w=800&q=80" # Shortcut bacon
    
    # VEAL PRODUCTS
    "veal/veal-schnitzel.jpg" = "https://images.unsplash.com/photo-1588168333986-5078d3ae3976?w=800&q=80" # Schnitzel
    "veal/veal-cutlets.jpg" = "https://images.unsplash.com/photo-1546833998-877b37c2e5c6?w=800&q=80" # Veal cutlets
    "veal/veal-osso-bucco.jpg" = "https://images.unsplash.com/photo-1603360946369-dc9bb6258143?w=800&q=80" # Osso bucco
    "veal/veal-shoulder.jpg" = "https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80" # Shoulder
    "veal/veal-mince.jpg" = "https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80" # Veal mince
    "veal/diced-veal.jpg" = "https://images.unsplash.com/photo-1546833998-877b37c2e5c6?w=800&q=80" # Diced veal
    
    # POULTRY PRODUCTS
    "poultry/chicken-breast-fillets.jpg" = "https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=800&q=80" # Chicken breast
    "poultry/chicken-thigh-fillets.jpg" = "https://images.unsplash.com/photo-1587593810167-a84920ea0781?w=800&q=80" # Thigh fillets
    "poultry/crumbed-chicken-fillets.jpg" = "https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=800&q=80" # Crumbed chicken
    "poultry/chicken-drumsticks.jpg" = "https://images.unsplash.com/photo-1587593810167-a84920ea0781?w=800&q=80" # Drumsticks
    "poultry/chicken-wings.jpg" = "https://images.unsplash.com/photo-1608039755401-742074f0548d?w=800&q=80" # Wings
    "poultry/whole-chicken.jpg" = "https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=800&q=80" # Whole chicken
    "poultry/chicken-mince.jpg" = "https://images.unsplash.com/photo-1587593810167-a84920ea0781?w=800&q=80" # Chicken mince
    "poultry/chicken-maryland.jpg" = "https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=800&q=80" # Maryland
    "poultry/chicken-nibbles.jpg" = "https://images.unsplash.com/photo-1608039755401-742074f0548d?w=800&q=80" # Nibbles
    "poultry/turkey-breast.jpg" = "https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=800&q=80" # Turkey breast
    "poultry/duck-breast.jpg" = "https://images.unsplash.com/photo-1587593810167-a84920ea0781?w=800&q=80" # Duck breast
    "poultry/whole-duck.jpg" = "https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=800&q=80" # Whole duck
    
    # SAUSAGES
    "sausages/beef-sausages.jpg" = "https://images.unsplash.com/photo-1612836431590-bc7328d96820?w=800&q=80" # Beef sausages
    "sausages/pork-sausages.jpg" = "https://images.unsplash.com/photo-1612836431590-bc7328d96820?w=800&q=80" # Pork sausages
    "sausages/thin-beef-sausages.jpg" = "https://images.unsplash.com/photo-1612836431590-bc7328d96820?w=800&q=80" # Thin beef
    "sausages/thin-pork-sausages.jpg" = "https://images.unsplash.com/photo-1612836431590-bc7328d96820?w=800&q=80" # Thin pork
    "sausages/lamb-sausages.jpg" = "https://images.unsplash.com/photo-1612836431590-bc7328d96820?w=800&q=80" # Lamb sausages
    "sausages/chicken-sausages.jpg" = "https://images.unsplash.com/photo-1612836431590-bc7328d96820?w=800&q=80" # Chicken sausages
    "sausages/chorizo-sausages.jpg" = "https://images.unsplash.com/photo-1612836431590-bc7328d96820?w=800&q=80" # Chorizo
    "sausages/italian-sausages.jpg" = "https://images.unsplash.com/photo-1612836431590-bc7328d96820?w=800&q=80" # Italian
    "sausages/boerewors.jpg" = "https://images.unsplash.com/photo-1612836431590-bc7328d96820?w=800&q=80" # Boerewors
    "sausages/bratwurst.jpg" = "https://images.unsplash.com/photo-1612836431590-bc7328d96820?w=800&q=80" # Bratwurst
    
    # DELI
    "deli/ham-leg.jpg" = "https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=800&q=80" # Ham
    "deli/shaved-ham.jpg" = "https://images.unsplash.com/photo-1562159278-1253a58da141?w=800&q=80" # Shaved ham
    "deli/devon.jpg" = "https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=800&q=80" # Devon
    "deli/salami.jpg" = "https://images.unsplash.com/photo-1562159278-1253a58da141?w=800&q=80" # Salami
    "deli/kabana.jpg" = "https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=800&q=80" # Kabana
    
    # MEALS
    "meals/beef-burgers.jpg" = "https://images.unsplash.com/photo-1551615593-ef5fe247e8f7?w=800&q=80" # Burgers
    "meals/lamb-kebabs.jpg" = "https://images.unsplash.com/photo-1574781330693-d062ceabe0ce?w=800&q=80" # Kebabs
    "meals/marinated-chicken.jpg" = "https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=800&q=80" # Marinated chicken
    "meals/beef-meatballs.jpg" = "https://images.unsplash.com/photo-1551615593-ef5fe247e8f7?w=800&q=80" # Meatballs
    
    # OTHER
    "other/kangaroo-fillet.jpg" = "https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80" # Kangaroo
    "other/venison-fillet.jpg" = "https://images.unsplash.com/photo-1546833998-877b37c2e5c6?w=800&q=80" # Venison
    "other/crocodile-fillet.jpg" = "https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80" # Crocodile
}

Write-Host "🥩 Downloading Premium Product Images for Zambezi Meats..." -ForegroundColor Green
Write-Host ""

$downloaded = 0
$skipped = 0
$failed = 0

foreach ($key in $images.Keys) {
    $filePath = Join-Path $baseDir $key
    $url = $images[$key]
    
    # Create directory if it doesn't exist
    $directory = Split-Path $filePath -Parent
    if (!(Test-Path $directory)) {
        New-Item -ItemType Directory -Force -Path $directory | Out-Null
    }
    
    # Skip if file already exists
    if (Test-Path $filePath) {
        Write-Host "  ⏭️  Skipped: $key (already exists)" -ForegroundColor Yellow
        $skipped++
        continue
    }
    
    try {
        Write-Host "  ⬇️  Downloading: $key" -ForegroundColor Cyan
        Invoke-WebRequest -Uri $url -OutFile $filePath -UseBasicParsing
        $downloaded++
    }
    catch {
        Write-Host "  ❌ Failed: $key - $($_.Exception.Message)" -ForegroundColor Red
        $failed++
    }
    
    # Small delay to avoid rate limiting
    Start-Sleep -Milliseconds 500
}

Write-Host ""
Write-Host "✅ Download Complete!" -ForegroundColor Green
Write-Host "  Downloaded: $downloaded files" -ForegroundColor Cyan
Write-Host "  Skipped: $skipped files" -ForegroundColor Yellow
Write-Host "  Failed: $failed files" -ForegroundColor Red
