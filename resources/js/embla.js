
import EmblaCarousel from 'embla-carousel';

document.addEventListener('DOMContentLoaded', () => {
    const emblaNode = document.querySelector('.embla');
    const embla = EmblaCarousel(emblaNode, {
        loop: true,
        align: 'center',
        skipSnaps: false,
        dragFree: false,
    });

    const slides = embla.slideNodes();

    function applyZoomEffect() {
        slides.forEach((slide, index) => {
            slide.querySelector('img').classList.remove('scale-100', 'scale-110');
            slide.querySelector('img').classList.add(
                embla.selectedScrollSnap() === index ? 'scale-110' : 'scale-100'
            );
        });
    }

    embla.on('select', applyZoomEffect);
    embla.on('init', applyZoomEffect);
});
