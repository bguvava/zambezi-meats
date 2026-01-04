# Download remaining product images with better URLs
$baseDir = "c:\xampp\htdocs\Zambezi-Meats\frontend\public\images\products"

# Alternative URLs for failed downloads
$images = @{
    # Lamb products that failed
    "lamb/leg-of-lamb.jpg" = "https://images.pexels.com/photos/6210747/pexels-photo-6210747.jpeg?auto=compress&cs=tinysrgb&w=800"
    "lamb/lamb-cutlets.jpg" = "https://images.pexels.com/photos/14752147/pexels-photo-14752147.jpeg?auto=compress&cs=tinysrgb&w=800"
    "lamb/lamb-chump-chops.jpg" = "https://images.pexels.com/photos/7218637/pexels-photo-7218637.jpeg?auto=compress&cs=tinysrgb&w=800"
    "lamb/lamb-forequarter-chops.jpg" = "https://images.pexels.com/photos/7218628/pexels-photo-7218628.jpeg?auto=compress&cs=tinysrgb&w=800"
    "lamb/lamb-shoulder.jpg" = "https://images.pexels.com/photos/7218606/pexels-photo-7218606.jpeg?auto=compress&cs=tinysrgb&w=800"
    "lamb/lamb-shanks.jpg" = "https://images.pexels.com/photos/4664528/pexels-photo-4664528.jpeg?auto=compress&cs=tinysrgb&w=800"
    "lamb/lamb-mince.jpg" = "https://images.pexels.com/photos/361184/asparagus-steak-veal-steak-veal-361184.jpeg?auto=compress&cs=tinysrgb&w=800"
    "lamb/diced-lamb.jpg" = "https://images.pexels.com/photos/3535383/pexels-photo-3535383.jpeg?auto=compress&cs=tinysrgb&w=800"
    "lamb/butterflied-leg.jpg" = "https://images.pexels.com/photos/6210742/pexels-photo-6210742.jpeg?auto=compress&cs=tinysrgb&w=800"
    
    # Beef that failed
    "beef/topside-steak.jpg" = "https://images.pexels.com/photos/361184/asparagus-steak-veal-steak-veal-361184.jpeg?auto=compress&cs=tinysrgb&w=800"
    
    # Sausages that failed
    "sausages/beef-sausages.jpg" = "https://images.pexels.com/photos/1639562/pexels-photo-1639562.jpeg?auto=compress&cs=tinysrgb&w=800"
    "sausages/pork-sausages.jpg" = "https://images.pexels.com/photos/3298702/pexels-photo-3298702.jpeg?auto=compress&cs=tinysrgb&w=800"
    "sausages/thin-beef-sausages.jpg" = "https://images.pexels.com/photos/5501095/pexels-photo-5501095.jpeg?auto=compress&cs=tinysrgb&w=800"
    "sausages/thin-pork-sausages.jpg" = "https://images.pexels.com/photos/3298702/pexels-photo-3298702.jpeg?auto=compress&cs=tinysrgb&w=800"
    "sausages/lamb-sausages.jpg" = "https://images.pexels.com/photos/1639562/pexels-photo-1639562.jpeg?auto=compress&cs=tinysrgb&w=800"
    "sausages/chicken-sausages.jpg" = "https://images.pexels.com/photos/5501095/pexels-photo-5501095.jpeg?auto=compress&cs=tinysrgb&w=800"
    "sausages/chorizo-sausages.jpg" = "https://images.pexels.com/photos/3298702/pexels-photo-3298702.jpeg?auto=compress&cs=tinysrgb&w=800"
    "sausages/italian-sausages.jpg" = "https://images.pexels.com/photos/1639562/pexels-photo-1639562.jpeg?auto=compress&cs=tinysrgb&w=800"
    "sausages/boerewors.jpg" = "https://images.pexels.com/photos/3298702/pexels-photo-3298702.jpeg?auto=compress&cs=tinysrgb&w=800"
    "sausages/bratwurst.jpg" = "https://images.pexels.com/photos/5501095/pexels-photo-5501095.jpeg?auto=compress&cs=tinysrgb&w=800"
    
    # Meals that failed
    "meals/lamb-kebabs.jpg" = "https://images.pexels.com/photos/16743486/pexels-photo-16743486.jpeg?auto=compress&cs=tinysrgb&w=800"
}

Write-Host "🥩 Downloading Remaining Product Images..." -ForegroundColor Green

$downloaded = 0
$failed = 0

foreach ($key in $images.Keys) {
    $filePath = Join-Path $baseDir $key
    $url = $images[$key]
    
    if (Test-Path $filePath) {
        Write-Host "  ⏭️  Skipped: $key (already exists)" -ForegroundColor Yellow
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
    
    Start-Sleep -Milliseconds 500
}

Write-Host ""
Write-Host "✅ Download Complete!" -ForegroundColor Green
Write-Host "  Downloaded: $downloaded files" -ForegroundColor Cyan
Write-Host "  Failed: $failed files" -ForegroundColor Red
