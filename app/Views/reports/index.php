<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Analytics & Reports<?= $this->endSection() ?>

<?= $this->section('header') ?>Analytics & Reports<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <!-- Welcome Header -->
    <div>
        <h2 class="text-2xl font-bold text-white tracking-tight">Clinic Performance Reports</h2>
        <p class="text-sm text-neutral-400 mt-1">Real-time operational trends and service revenue insights for the last 30 days.</p>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Revenue Trends SVG Line Chart -->
        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg space-y-4">
            <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Revenue Summary</h3>
                    <p class="text-xs text-neutral-500 mt-0.5">Daily settled payments (last 30 days)</p>
                </div>
                <span class="text-xs font-bold text-emerald-450 bg-emerald-950/40 border border-emerald-500/20 px-2.5 py-0.5 rounded-full">
                    Total: Rp<?= number_format(array_sum($revenueTrends), 0, ',', '.') ?>
                </span>
            </div>

            <!-- SVG Line Graph -->
            <div class="relative pt-2">
                <?php
                $revValues = array_values($revenueTrends);
                $revKeys = array_keys($revenueTrends);
                $maxRev = max(max($revValues), 100000); // Prevent divide by zero, min 100k
                
                // SVG dimensions
                $width = 1000;
                $height = 300;
                $paddingLeft = 60;
                $paddingRight = 20;
                $paddingTop = 30;
                $paddingBottom = 40;
                
                $chartWidth = $width - $paddingLeft - $paddingRight;
                $chartHeight = $height - $paddingTop - $paddingBottom;
                
                // Construct points
                $points = [];
                $areaPoints = [];
                $circles = [];
                
                // Start of area path (bottom left of chart area)
                $areaPoints[] = "$paddingLeft," . ($height - $paddingBottom);
                
                for ($i = 0; $i < 30; $i++) {
                    $amount = $revValues[$i];
                    $x = $paddingLeft + ($i / 29) * $chartWidth;
                    $y = ($height - $paddingBottom) - ($amount / $maxRev) * $chartHeight;
                    
                    $points[] = "$x,$y";
                    $areaPoints[] = "$x,$y";
                    
                    // Keep track of coordinates for hover tooltips
                    $circles[] = [
                        'x' => $x,
                        'y' => $y,
                        'date' => date('M j', strtotime($revKeys[$i])),
                        'val' => 'Rp' . number_format($amount, 0, ',', '.')
                    ];
                }
                
                // End of area path (bottom right of chart area)
                $areaPoints[] = ($paddingLeft + $chartWidth) . "," . ($height - $paddingBottom);
                
                $polylinePoints = implode(' ', $points);
                $areaPathPoints = "M" . implode(' L', $areaPoints) . " Z";
                ?>
                <svg viewBox="0 0 <?= $width ?> <?= $height ?>" class="w-full h-auto text-neutral-600 overflow-visible" x-data="{ hoverIndex: null }">
                    <defs>
                        <!-- Area gradient -->
                        <linearGradient id="areaGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#10b981" stop-opacity="0.25"/>
                            <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>

                    <!-- Horizontal Gridlines -->
                    <?php for ($g = 0; $g <= 4; $g++): 
                        $gridY = ($height - $paddingBottom) - ($g / 4) * $chartHeight;
                        $gridVal = ($g / 4) * $maxRev;
                    ?>
                        <line x1="<?= $paddingLeft ?>" y1="<?= $gridY ?>" x2="<?= $width - $paddingRight ?>" y2="<?= $gridY ?>" stroke="#262626" stroke-width="1" stroke-dasharray="4,4" />
                        <text x="<?= $paddingLeft - 10 ?>" y="<?= $gridY + 4 ?>" fill="#737373" font-size="20" text-anchor="end">Rp<?= number_format($gridVal/1000, 0) ?>k</text>
                    <?php endfor; ?>

                    <!-- Area under curve -->
                    <path d="<?= $areaPathPoints ?>" fill="url(#areaGradient)" />

                    <!-- Line curve -->
                    <polyline points="<?= $polylinePoints ?>" fill="none" stroke="#10b981" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />

                    <!-- X-Axis Labels (draw 6 labels) -->
                    <?php for ($k = 0; $k < 6; $k++): 
                        $idx = floor(($k / 5) * 29);
                        $lblX = $paddingLeft + ($idx / 29) * $chartWidth;
                        $lblText = date('M j', strtotime($revKeys[$idx]));
                    ?>
                        <text x="<?= $lblX ?>" y="<?= $height - $paddingBottom + 25 ?>" fill="#737373" font-size="20" text-anchor="middle"><?= $lblText ?></text>
                    <?php endfor; ?>

                    <!-- Invisible Interactive overlay triggers -->
                    <?php foreach ($circles as $idx => $c): ?>
                        <!-- Dot -->
                        <circle cx="<?= $c['x'] ?>" cy="<?= $c['y'] ?>" r="6" fill="#10b981" stroke="#0a0a0a" stroke-width="2" 
                                class="transition duration-150 transform hover:scale-150 cursor-pointer"
                                :class="hoverIndex === <?= $idx ?> ? 'opacity-100 scale-150' : 'opacity-0 hover:opacity-100'"
                                @mouseenter="hoverIndex = <?= $idx ?>"
                                @mouseleave="hoverIndex = null" />

                        <!-- Large invisible hover zone -->
                        <rect x="<?= $c['x'] - 15 ?>" y="<?= $paddingTop ?>" width="30" height="<?= $chartHeight ?>" fill="transparent" class="cursor-pointer"
                              @mouseenter="hoverIndex = <?= $idx ?>"
                              @mouseleave="hoverIndex = null" />
                    <?php endforeach; ?>

                    <!-- Tooltip Card inside SVG -->
                    <g x-show="hoverIndex !== null" x-cloak class="pointer-events-none">
                        <?php foreach ($circles as $idx => $c): ?>
                            <g x-show="hoverIndex === <?= $idx ?>">
                                <!-- Tooltip Box Background -->
                                <rect x="<?= min($c['x'] - 80, $width - 180) ?>" y="<?= max($c['y'] - 65, 10) ?>" width="160" height="50" rx="8" fill="#171717" stroke="#404040" stroke-width="1.5" />
                                <!-- Tooltip Content -->
                                <text x="<?= min($c['x'], $width - 100) ?>" y="<?= max($c['y'] - 48, 27) ?>" fill="#a3a3a3" font-size="16" font-weight="bold" text-anchor="middle"><?= $c['date'] ?></text>
                                <text x="<?= min($c['x'], $width - 100) ?>" y="<?= max($c['y'] - 27, 48) ?>" fill="#ffffff" font-size="18" font-weight="extrabold" text-anchor="middle"><?= $c['val'] ?></text>
                            </g>
                        <?php endforeach; ?>
                    </g>
                </svg>
            </div>
        </div>

        <!-- Visit Trends SVG Bar Chart -->
        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg space-y-4">
            <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Patient Volume</h3>
                    <p class="text-xs text-neutral-500 mt-0.5">Daily checked-in visits (last 30 days)</p>
                </div>
                <span class="text-xs font-bold text-brand-400 bg-brand-500/10 border border-brand-500/20 px-2.5 py-0.5 rounded-full">
                    Total: <?= array_sum($visitTrends) ?> visits
                </span>
            </div>

            <!-- SVG Bar Graph -->
            <div class="relative pt-2">
                <?php
                $vValues = array_values($visitTrends);
                $vKeys = array_keys($visitTrends);
                $maxVisits = max(max($vValues), 5); // Prevents divide by zero, min 5
                
                $vWidth = 1000;
                $vHeight = 300;
                $vPaddingLeft = 40;
                $vPaddingRight = 20;
                $vPaddingTop = 30;
                $vPaddingBottom = 40;
                
                $vChartWidth = $vWidth - $vPaddingLeft - $vPaddingRight;
                $vChartHeight = $vHeight - $vPaddingTop - $vPaddingBottom;
                
                $barWidth = ($vChartWidth / 30) * 0.65;
                $barSpacing = ($vChartWidth / 30) * 0.35;
                
                $bars = [];
                for ($i = 0; $i < 30; $i++) {
                    $visits = $vValues[$i];
                    $barH = ($visits / $maxVisits) * $vChartHeight;
                    $x = $vPaddingLeft + $i * ($barWidth + $barSpacing);
                    $y = ($vHeight - $vPaddingBottom) - $barH;
                    
                    $bars[] = [
                        'x' => $x,
                        'y' => $y,
                        'w' => $barWidth,
                        'h' => max($barH, 2), // Draw at least a tiny slip if visits is 0 or low
                        'date' => date('M j', strtotime($vKeys[$i])),
                        'val' => $visits . ' visit' . ($visits != 1 ? 's' : '')
                    ];
                }
                ?>
                <svg viewBox="0 0 <?= $vWidth ?> <?= $vHeight ?>" class="w-full h-auto text-neutral-600 overflow-visible" x-data="{ hoverBarIndex: null }">
                    <!-- Horizontal Gridlines -->
                    <?php for ($g = 0; $g <= 4; $g++): 
                        $gridY = ($vHeight - $vPaddingBottom) - ($g / 4) * $vChartHeight;
                        $gridVal = round(($g / 4) * $maxVisits);
                    ?>
                        <line x1="<?= $vPaddingLeft ?>" y1="<?= $gridY ?>" x2="<?= $vWidth - $vPaddingRight ?>" y2="<?= $gridY ?>" stroke="#262626" stroke-width="1" stroke-dasharray="4,4" />
                        <text x="<?= $vPaddingLeft - 10 ?>" y="<?= $gridY + 4 ?>" fill="#737373" font-size="20" text-anchor="end"><?= $gridVal ?></text>
                    <?php endfor; ?>

                    <!-- Columns -->
                    <?php foreach ($bars as $idx => $b): ?>
                        <rect x="<?= $b['x'] ?>" y="<?= $b['y'] ?>" width="<?= $b['w'] ?>" height="<?= $b['h'] ?>" rx="3" ry="3"
                              fill="#6366f1" class="transition duration-150 cursor-pointer"
                              :fill="hoverBarIndex === <?= $idx ?> ? '#818cf8' : '#6366f1'"
                              @mouseenter="hoverBarIndex = <?= $idx ?>"
                              @mouseleave="hoverBarIndex = null" />
                    <?php endforeach; ?>

                    <!-- X-Axis Labels (draw 6 labels) -->
                    <?php for ($k = 0; $k < 6; $k++): 
                        $idx = floor(($k / 5) * 29);
                        $lblX = $bars[$idx]['x'] + $barWidth / 2;
                        $lblText = date('M j', strtotime($vKeys[$idx]));
                    ?>
                        <text x="<?= $lblX ?>" y="<?= $vHeight - $vPaddingBottom + 25 ?>" fill="#737373" font-size="20" text-anchor="middle"><?= $lblText ?></text>
                    <?php endfor; ?>

                    <!-- Tooltip Card inside SVG -->
                    <g x-show="hoverBarIndex !== null" x-cloak class="pointer-events-none">
                        <?php foreach ($bars as $idx => $b): ?>
                            <g x-show="hoverBarIndex === <?= $idx ?>">
                                <!-- Tooltip Box Background -->
                                <rect x="<?= min($b['x'] - 60, $vWidth - 140) ?>" y="<?= max($b['y'] - 65, 10) ?>" width="120" height="50" rx="8" fill="#171717" stroke="#404040" stroke-width="1.5" />
                                <!-- Tooltip Content -->
                                <text x="<?= min($b['x'] + $b['w']/2, $vWidth - 80) ?>" y="<?= max($b['y'] - 48, 27) ?>" fill="#a3a3a3" font-size="16" font-weight="bold" text-anchor="middle"><?= $b['date'] ?></text>
                                <text x="<?= min($b['x'] + $b['w']/2, $vWidth - 80) ?>" y="<?= max($b['y'] - 27, 48) ?>" fill="#ffffff" font-size="18" font-weight="extrabold" text-anchor="middle"><?= $b['val'] ?></text>
                            </g>
                        <?php endforeach; ?>
                    </g>
                </svg>
            </div>
        </div>

    </div>

    <!-- Top Performing Services -->
    <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-neutral-800 pb-3 mb-4">Top Billing Services</h3>
        
        <?php if (empty($topServices)): ?>
            <div class="p-6 bg-neutral-950 border border-neutral-850 rounded-2xl text-center">
                <p class="text-xs text-neutral-500">No medical billing activities have been recorded yet.</p>
            </div>
        <?php else: ?>
            <div class="overflow-hidden border border-neutral-850 rounded-2xl">
                <table class="min-w-full divide-y divide-neutral-800 bg-neutral-950/20 text-xs">
                    <thead class="bg-neutral-950/80">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Service Name</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Catalog Code</th>
                            <th scope="col" class="px-6 py-4 class text-right text-xs font-bold text-neutral-400 uppercase tracking-wider">Unit Price</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-neutral-400 uppercase tracking-wider">Quantity Rendered</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-neutral-400 uppercase tracking-wider">Total Revenue Generated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-800">
                        <?php foreach ($topServices as $srv): ?>
                            <tr class="hover:bg-neutral-800/10 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-white font-semibold">
                                    <?= esc($srv['name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-neutral-400 font-mono font-bold uppercase text-[10px]">
                                    <?= esc($srv['code']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-neutral-350">
                                    Rp<?= number_format($srv['price'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-neutral-350">
                                    <?= esc($srv['total_qty']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-brand-400 font-bold">
                                    Rp<?= number_format($srv['total_revenue'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
