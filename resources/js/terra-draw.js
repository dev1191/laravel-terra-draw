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

/**
 * Initialize a single TerraDraw instance on a container element.
 */
export function initTerraDraw({
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
    const mapLib = typeof maplibregl !== "undefined" ? maplibregl : window.maplibregl;

    if (!mapLib) {
        console.error("[laravel-terra-draw] MapLibre GL is not loaded.");
        return null;
    }

    const targetEl = typeof container === "string" ? document.getElementById(container) : container;
    if (!targetEl) {
        console.error(`[laravel-terra-draw] Container #${container} not found.`);
        return null;
    }

    const map = new mapLib.Map({
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
                            feature: {
                                draggable: true,
                                rotateable: true,
                                scaleable: true,
                                coordinates: {
                                    midpoints: true,
                                    draggable: true,
                                    deletable: true,
                                },
                            },
                        },
                        linestring: {
                            feature: {
                                draggable: true,
                                coordinates: {
                                    midpoints: true,
                                    draggable: true,
                                    deletable: true,
                                },
                            },
                        },
                        point: {
                            feature: {
                                draggable: true,
                            },
                        },
                        freehand: {
                            feature: {
                                draggable: true,
                            },
                        },
                        circle: {
                            feature: {
                                draggable: true,
                                scaleable: true,
                            },
                        },
                        rectangle: {
                            feature: {
                                draggable: true,
                                rotateable: true,
                                scaleable: true,
                            },
                        },
                    },
                })
            );
        }
    }

    const draw = new TerraDraw({
        adapter: new TerraDrawMapLibreGLAdapter({ map, lib: mapLib }),
        modes: modeInstances,
    });

    const startDraw = () => {
        if (!draw.enabled) {
            draw.start();

            // Load initial GeoJSON if provided
            if (initialData) {
                try {
                    const parsed = typeof initialData === "string" ? JSON.parse(initialData) : initialData;
                    if (parsed && parsed.features && Array.isArray(parsed.features)) {
                        draw.addFeatures(parsed.features);
                    } else if (parsed && parsed.type === "Feature") {
                        draw.addFeatures([parsed]);
                    }
                } catch (e) {
                    console.warn("[laravel-terra-draw] Could not parse initial GeoJSON data", e);
                }
            }

            if (editable && initialMode && modes.includes(initialMode)) {
                try {
                    draw.setMode(initialMode);
                } catch (e) {
                    console.warn(`[laravel-terra-draw] Failed to set initial mode: ${initialMode}`, e);
                }
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
        if (typeof onChange === "function") {
            onChange(snapshot, type, ids);
        }
    });

    return { map, draw };
}

/**
 * Automatically find and initialize all [data-terra-draw] elements on the page.
 */
export function autoInitTerraDraw() {
    const elements = document.querySelectorAll("[data-terra-draw]:not([data-terra-draw-initialized])");

    elements.forEach((el) => {
        el.setAttribute("data-terra-draw-initialized", "true");

        const id = el.getAttribute("data-id") || el.id;
        const name = el.getAttribute("data-name");
        const center = JSON.parse(el.getAttribute("data-center") || "[0, 0]");
        const zoom = parseFloat(el.getAttribute("data-zoom") || "2");
        const mapStyle = el.getAttribute("data-map-style") || "https://demotiles.maplibre.org/style.json";
        const modes = JSON.parse(el.getAttribute("data-modes") || '["polygon","rectangle","circle","linestring","freehand","point","select"]');
        const initialMode = el.getAttribute("data-initial-mode") || "polygon";
        const editable = el.getAttribute("data-editable") !== "false";

        // Find associated hidden input
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
                    hiddenInput.value = snapshot && snapshot.features && snapshot.features.length > 0
                        ? JSON.stringify(snapshot)
                        : "";
                    // Trigger input and change events for Livewire / Vue / Alpine
                    hiddenInput.dispatchEvent(new Event("input", { bubbles: true }));
                    hiddenInput.dispatchEvent(new Event("change", { bubbles: true }));
                }

                el.dispatchEvent(new CustomEvent("terra-draw:change", {
                    bubbles: true,
                    detail: { snapshot, type, ids },
                }));
            },
        });

        if (!instance) return;

        const { draw } = instance;

        // Hook up toolbar buttons if present
        const toolbar = document.querySelector(`[data-terra-draw-toolbar="${id}"]`);
        if (toolbar && draw) {
            const buttons = toolbar.querySelectorAll(".terra-draw-btn[data-mode]");
            buttons.forEach((btn) => {
                btn.addEventListener("click", () => {
                    const mode = btn.getAttribute("data-mode");
                    try {
                        draw.setMode(mode);
                        buttons.forEach((b) => b.classList.remove("active"));
                        btn.classList.add("active");
                    } catch (e) {
                        console.error(`[laravel-terra-draw] Error setting mode: ${mode}`, e);
                    }
                });
            });

            const clearBtn = toolbar.querySelector('.terra-draw-btn[data-action="clear"]');
            if (clearBtn) {
                clearBtn.addEventListener("click", () => {
                    try {
                        draw.clear();
                        if (hiddenInput) {
                            hiddenInput.value = "";
                            hiddenInput.dispatchEvent(new Event("input", { bubbles: true }));
                            hiddenInput.dispatchEvent(new Event("change", { bubbles: true }));
                        }
                    } catch (e) {
                        console.error("[laravel-terra-draw] Error clearing draw:", e);
                    }
                });
            }
        }

        el.dispatchEvent(new CustomEvent("terra-draw:ready", {
            bubbles: true,
            detail: instance,
        }));
    });
}

// Auto-run when DOM is loaded
if (typeof document !== "undefined") {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => autoInitTerraDraw());
    } else {
        autoInitTerraDraw();
    }
}