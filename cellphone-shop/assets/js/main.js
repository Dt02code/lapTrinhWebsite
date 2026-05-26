document.querySelectorAll('.qty-btn').forEach((btn) => {
  btn.addEventListener('click', () => {
    const input = btn.parentElement.querySelector('input[name="qty"]');
    if (!input) return;
    let val = parseInt(input.value, 10) || 1;
    if (btn.dataset.action === 'plus') val++;
    else val = Math.max(1, val - 1);
    input.value = val;
    btn.closest('form')?.submit();
  });
});
