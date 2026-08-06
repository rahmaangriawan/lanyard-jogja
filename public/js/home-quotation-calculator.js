(() => {
  const calculator = document.querySelector('[data-quotation-calculator]');
  if (!calculator) return;

  const form = calculator.querySelector('[data-quotation-form]');
  const packageInputs = Array.from(form.querySelectorAll('input[name="lanyard_size"]'));
  const quantity = form.querySelector('input[name="quantity"]');
  const material = form.querySelector('select[name="material"]');
  const decrease = form.querySelector('[data-quotation-decrease]');
  const increase = form.querySelector('[data-quotation-increase]');
  const total = form.querySelector('[data-quotation-total]');
  const breakdown = form.querySelector('[data-quotation-breakdown]');
  const quotationLink = form.querySelector('[data-quotation-link]');
  const minimum = Number(quantity.min || 50);
  const maximum = Number(quantity.max || 100000);
  const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

  const safeQuantity = () => {
    const parsed = Number.parseInt(quantity.value, 10);
    return Number.isFinite(parsed) ? Math.min(Math.max(parsed, minimum), maximum) : minimum;
  };
  const selectedPackage = () => packageInputs.find((input) => input.checked) || packageInputs[0];

  const update = () => {
    const selected = selectedPackage();
    const price = Number(selected.dataset.packagePrice || 0);
    const amount = safeQuantity();
    const label = selected.dataset.packageLabel || 'Lanyard custom';
    quantity.value = amount;
    total.textContent = formatter.format(price * amount);
    breakdown.textContent = `${amount.toLocaleString('id-ID')} pcs × ${formatter.format(price)}`;
    packageInputs.forEach((input) => input.closest('.home-quotation-calculator__package')?.classList.toggle('is-selected', input === selected));
    const message = [
      'Halo Lanyard Jogja, saya ingin meminta quotation.',
      `Ukuran: ${label}`,
      `Jumlah: ${amount.toLocaleString('id-ID')} pcs`,
      `Bahan: ${material.value}`,
      `Estimasi paket dasar: ${formatter.format(price * amount)} (${formatter.format(price)}/pcs).`,
      'Mohon dibantu konfirmasi harga final dan spesifikasinya.',
    ].join('\n');
    quotationLink.href = `https://wa.me/6282210200700?text=${encodeURIComponent(message)}`;
  };

  packageInputs.forEach((input) => input.addEventListener('change', update));
  form.addEventListener('submit', (event) => event.preventDefault());
  quantity.addEventListener('input', update);
  quantity.addEventListener('blur', update);
  material.addEventListener('change', update);
  decrease.addEventListener('click', () => { quantity.value = safeQuantity() - 1; update(); });
  increase.addEventListener('click', () => { quantity.value = safeQuantity() + 1; update(); });
  update();
})();
