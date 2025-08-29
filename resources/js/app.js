// app.js
import './bootstrap';
import Alpine from 'alpinejs';
import './embla.js';
import $ from 'jquery';
import 'slick-carousel';
import 'slick-carousel/slick/slick.css';        // CSS
import 'slick-carousel/slick/slick-theme.css';

window.Alpine = Alpine;
Alpine.start();

/**
 * Open Razorpay checkout with real order data
 * @param {{ amount: number, currency: string, order_id: string }} orderData
 * @param {{ name:string, contact:string }} prefill
 * @param {Function} onSuccess
 * @param {Function} onDismiss
 */
export function openRazorpay(orderData, prefill, onSuccess, onDismiss = () => {}) {
  const options = {
    key: window.razorpayKey,
    amount: orderData.amount,
    currency: orderData.currency,
    order_id: orderData.order_id,
    handler: onSuccess,
    prefill,
    theme: { color: '#116631' },
    modal: { ondismiss: onDismiss },
  };
  const rzp = new window.Razorpay(options);
  rzp.open();
}

$(document).ready(function() {
  $('.center').slick({
    centerMode: true,
    centerPadding: '60px',
    slidesToShow: 3,
    focusOnSelect: true,
    responsive: [
      { breakpoint: 768, settings: { arrows: false, centerMode: true, centerPadding: '40px', slidesToShow: 3 } },
      { breakpoint: 480, settings: { arrows: false, centerMode: true, centerPadding: '40px', slidesToShow: 1 } }
    ]
  });
});
