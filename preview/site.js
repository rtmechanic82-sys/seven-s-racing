document.addEventListener('DOMContentLoaded',()=>{const b=document.querySelector('.menu-toggle'),n=document.querySelector('.site-nav');if(b&&n)b.addEventListener('click',()=>{const open=n.classList.toggle('is-open');b.setAttribute('aria-expanded',String(open));b.textContent=open?'×':'☰';});});

