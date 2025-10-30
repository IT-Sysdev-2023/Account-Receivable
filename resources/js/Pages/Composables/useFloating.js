import {
    computePosition,
    autoUpdate,
    offset,
    shift,
    flip,
    arrow,
} from "@floating-ui/dom";

export function useFloating(options = {}) {
    const defaultOptions = {
        placement: "bottom-start",
        offset: 5,
        shiftPadding: 5,
        flipPadding: 5,
        ...options,
    };

    const floating = async (referenceEl, floatingEl, arrowEl = null) => {
        if (!referenceEl || !floatingEl) return;

        // Auto-update position when scrolling/resizing
        const cleanup = autoUpdate(referenceEl, floatingEl, async () => {
            const middleware = [
                offset(defaultOptions.offset),
                shift({ padding: defaultOptions.shiftPadding }),
                flip({ padding: defaultOptions.flipPadding }),
            ];

            if (arrowEl) {
                middleware.push(arrow({ element: arrowEl }));
            }

            const { x, y, placement, middlewareData } = await computePosition(
                referenceEl,
                floatingEl,
                {
                    placement: defaultOptions.placement,
                    middleware,
                }
            );

            Object.assign(floatingEl.style, {
                left: `${x}px`,
                top: `${y}px`,
            });

            if (arrowEl && middlewareData.arrow) {
                const { x: arrowX, y: arrowY } = middlewareData.arrow;
                Object.assign(arrowEl.style, {
                    left: arrowX != null ? `${arrowX}px` : "",
                    top: arrowY != null ? `${arrowY}px` : "",
                });
            }
        });

        return cleanup;
    };

    return { floating };
}
