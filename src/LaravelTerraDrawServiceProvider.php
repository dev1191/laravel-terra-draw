<?php

namespace DevRajThapa\LaravelTerraDraw;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelTerraDrawServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-terra-draw')
            ->hasConfigFile('terra-draw')
            ->hasViews('laravel-terra-draw')
            ->hasAssets();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(TerraDraw::class, function ($app) {
            return new TerraDraw(config('terra-draw', []));
        });
    }

    public function packageBooted(): void
    {
        // Register Blade directive for styles
        Blade::directive('terraDrawStyles', function () {
            return <<<'HTML'
<?php echo '
<link href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" rel="stylesheet" />
<style>
.terra-draw-wrapper { box-sizing: border-box; font-family: inherit; }
.terra-draw-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; padding: 8px; background: rgba(15, 23, 42, 0.9); border-radius: 8px 8px 0 0; border: 1px solid #334155; border-bottom: none; }
.terra-draw-btn { background: transparent; color: #94a3b8; border: 1px solid #475569; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.15s ease-in-out; }
.terra-draw-btn:hover { color: #ffffff; background: #334155; }
.terra-draw-btn.active { background: #6366f1; color: #ffffff; border-color: #6366f1; font-weight: 600; }
.terra-draw-btn-clear { border-color: rgba(239, 68, 68, 0.4); color: #f87171; }
.terra-draw-btn-clear:hover { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }
</style>
'; ?>
HTML;
        });

        // Register Blade directive for scripts
        Blade::directive('terraDrawScripts', function () {
            return <<<'HTML'
<?php echo '
<script type="importmap">
{
  "imports": {
    "terra-draw": "https://esm.sh/terra-draw@1.32.3",
    "terra-draw-maplibre-gl-adapter": "https://esm.sh/terra-draw-maplibre-gl-adapter@1.4.1",
    "maplibre-gl": "https://esm.sh/maplibre-gl@4.7.1"
  }
}
</script>
<script type="module">
import {
    TerraDraw,
    TerraDrawPolygonMode,
    TerraDrawPointMode,
    TerraDrawLineStringMode,
    TerraDrawFreehandMode,
    TerraDrawCircleMode,
    TerraDrawRectangleMode,
    TerraDrawSelectMode,
    TerraDrawRenderMode,
} from "terra-draw";
import { TerraDrawMapLibreGLAdapter } from "terra-draw-maplibre-gl-adapter";
import maplibregl from "maplibre-gl";

function initTerraDraw({
    container,
    mapStyle = "https://demotiles.maplibre.org/style.json",
    modes = ["polygon", "rectangle", "circle", "linestring", "freehand", "point", "select"],
    initialMode = "polygon",
    center = [0, 0],
    zoom = 2,
    initialData = null,
    editable = true,
    onChange = null,
} = {}) {
    const targetEl = typeof container === "string" ? document.getElementById(container) : container;
    if (!targetEl) return null;

    const map = new maplibregl.Map({
        container: targetEl,
        style: mapStyle,
        center: Array.isArray(center) ? center : [0, 0],
        zoom: Number(zoom) || 2,
    });

    const modeInstances = [new TerraDrawRenderMode({ modeName: "render" })];

    if (editable) {
        if (modes.includes("polygon")) modeInstances.push(new TerraDrawPolygonMode());
        if (modes.includes("rectangle")) modeInstances.push(new TerraDrawRectangleMode());
        if (modes.includes("circle")) modeInstances.push(new TerraDrawCircleMode());
        if (modes.includes("linestring")) modeInstances.push(new TerraDrawLineStringMode());
        if (modes.includes("freehand")) modeInstances.push(new TerraDrawFreehandMode());
        if (modes.includes("point")) modeInstances.push(new TerraDrawPointMode());
        if (modes.includes("select")) {
            modeInstances.push(
                new TerraDrawSelectMode({
                    flags: {
                        polygon: {
                            feature: { draggable: true, rotateable: true, scaleable: true, coordinates: { midpoints: true, draggable: true, deletable: true } }
                        },
                        linestring: {
                            feature: { draggable: true, coordinates: { midpoints: true, draggable: true, deletable: true } }
                        },
                        point: { feature: { draggable: true } },
                        freehand: { feature: { draggable: true } },
                        circle: { feature: { draggable: true, scaleable: true } },
                        rectangle: { feature: { draggable: true, rotateable: true, scaleable: true } },
                    },
                })
            );
        }
    }

    const draw = new TerraDraw({
        adapter: new TerraDrawMapLibreGLAdapter({ map, lib: maplibregl }),
        modes: modeInstances,
    });

    const startDraw = () => {
        if (!draw.enabled) {
            draw.start();
            if (initialData) {
                try {
                    const parsed = typeof initialData === "string" ? JSON.parse(initialData) : initialData;
                    if (parsed && parsed.features && Array.isArray(parsed.features)) {
                        draw.addFeatures(parsed.features);
                    } else if (parsed && parsed.type === "Feature") {
                        draw.addFeatures([parsed]);
                    }
                } catch (e) {}
            }
            if (editable && initialMode && modes.includes(initialMode)) {
                try { draw.setMode(initialMode); } catch (e) {}
            } else if (!editable) {
                draw.setMode("render");
            }
        }
    };

    if (map.loaded()) {
        startDraw();
    } else {
        map.on("load", startDraw);
    }

    draw.on("change", (ids, type) => {
        const snapshot = draw.getSnapshot();
        if (typeof onChange === "function") onChange(snapshot, type, ids);
    });

    return { map, draw };
}

function autoInitTerraDraw() {
    const elements = document.querySelectorAll("[data-terra-draw]:not([data-terra-draw-initialized])");
    elements.forEach((el) => {
        el.setAttribute("data-terra-draw-initialized", "true");
        const id = el.getAttribute("data-id") || el.id;
        const name = el.getAttribute("data-name");
        const center = JSON.parse(el.getAttribute("data-center") || "[0, 0]");
        const zoom = parseFloat(el.getAttribute("data-zoom") || "2");
        const mapStyle = el.getAttribute("data-map-style") || "https://demotiles.maplibre.org/style.json";
        const modes = JSON.parse(el.getAttribute("data-modes") || "[]");
        const initialMode = el.getAttribute("data-initial-mode") || "polygon";
        const editable = el.getAttribute("data-editable") !== "false";

        const hiddenInput = document.querySelector(`[data-terra-draw-input="${id}"]`) || document.querySelector(`input[name="${name}"]`);
        const initialData = hiddenInput && hiddenInput.value ? hiddenInput.value : null;

        const instance = initTerraDraw({
            container: el,
            mapStyle,
            modes,
            initialMode,
            center,
            zoom,
            initialData,
            editable,
            onChange: (snapshot, type, ids) => {
                if (hiddenInput) {
                    hiddenInput.value = snapshot && snapshot.features && snapshot.features.length > 0 ? JSON.stringify(snapshot) : "";
                    hiddenInput.dispatchEvent(new Event("input", { bubbles: true }));
                    hiddenInput.dispatchEvent(new Event("change", { bubbles: true }));
                }
                el.dispatchEvent(new CustomEvent("terra-draw:change", { bubbles: true, detail: { snapshot, type, ids } }));
            },
        });

        if (!instance) return;
        const { draw } = instance;

        const toolbar = document.querySelector(`[data-terra-draw-toolbar="${id}"]`);
        if (toolbar && draw) {
            toolbar.querySelectorAll(".terra-draw-btn[data-mode]").forEach((btn) => {
                btn.addEventListener("click", () => {
                    const mode = btn.getAttribute("data-mode");
                    try {
                        draw.setMode(mode);
                        toolbar.querySelectorAll(".terra-draw-btn").forEach((b) => b.classList.remove("active"));
                        btn.classList.add("active");
                    } catch (e) { console.error(e); }
                });
            });

            const clearBtn = toolbar.querySelector(".terra-draw-btn-clear");
            if (clearBtn) {
                clearBtn.addEventListener("click", () => {
                    try {
                        draw.clear();
                        if (hiddenInput) {
                            hiddenInput.value = "";
                            hiddenInput.dispatchEvent(new Event("input", { bubbles: true }));
                            hiddenInput.dispatchEvent(new Event("change", { bubbles: true }));
                        }
                    } catch (e) { console.error(e); }
                });
            }
        }
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", autoInitTerraDraw);
} else {
    autoInitTerraDraw();
}
</script>
'; ?>
HTML;
        });
    }
}
