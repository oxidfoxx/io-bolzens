// document.addEventListener('DOMContentLoaded', () => {
//   const telInput = document.getElementById('tel_num');
//   const usernameInput = document.getElementById('username');

//   if (telInput) {
//     // Подстановка "+7 " при фокусе, если поле пустое
//     telInput.addEventListener('focus', (e) => {
//       if (!e.target.value) {
//         e.target.value = '+7 ';
//       }
//     });

//     // Очистка поля при потере фокуса, если там осталось только "+7 "
//     telInput.addEventListener('blur', (e) => {
//       if (e.target.value.trim() === '+7') {
//         e.target.value = '';
//       }
//     });

//     // Нативная маска телефона +7 (999) 999-99-99
//     telInput.addEventListener('input', (e) => {
//       let matrix = '+7 (___) ___-__-__',
//           i = 0,
//           def = matrix.replace(/\D/g, ''),
//           val = e.target.value.replace(/\D/g, '');
      
//       // Запрещаем удалять начальную семерку
//       if (val.length === 0 || val[0] !== '7') {
//         val = '7' + val;
//       }
      
//       if (def.length >= val.length) val = def;
      
//       e.target.value = matrix.replace(/./g, (a) => {
//         return /[_\d]/.test(a) && i < val.length ? val.charAt(i++) : i >= val.length ? '' : a;
//       });
//     });
//   }

//   if (usernameInput) {
//     usernameInput.addEventListener('input', (e) => {
//       e.target.value = e.target.value.replace(/[^а-яёА-ЯЁ ]/g, '');
//     });
//   }
// });

function togglePasswordVisibility(button, inputId) {
  const input = document.getElementById(inputId);
  if (!input) return false;

  const isPassword = input.type === 'password';
  input.type = isPassword ? 'text' : 'password';
  button.classList.toggle('view', isPassword);
  
  return false;
}

function checkPass() {
  const password = document.getElementById('password')?.value;
  const confirmPassword = document.getElementById('confirm-password')?.value;

  if (password !== confirmPassword) {
    alert('Пароли не совпадают!');
    return false;
  }
  return true;
}
