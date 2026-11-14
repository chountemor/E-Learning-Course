document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.password-toggle').forEach(function(btn){
        btn.addEventListener('click', function(){
            const input=document.getElementById(btn.dataset.target); if(!input) return;
            const icon=btn.querySelector('i'); const show=input.type==='password';
            input.type=show?'text':'password';
            icon.className=show?'fa-regular fa-eye-slash':'fa-regular fa-eye';
            btn.setAttribute('aria-label',show?'Hide password':'Show password');
            input.focus();
        });
    });
    const pw=document.getElementById('password'), status=document.getElementById('passwordStatus');
    if(pw&&status){
        pw.addEventListener('input',function(){
            const dot=status.querySelector('.status-dot'), text=status.querySelector('span:last-child');
            if(!pw.value){dot.className='status-dot'; text.textContent='Enter your password'; status.className='password-status';}
            else if(pw.value.length>=6){dot.className='status-dot valid'; text.textContent='Password entered'; status.className='password-status valid';}
            else {dot.className='status-dot invalid'; text.textContent='Password should be at least 6 characters'; status.className='password-status invalid';}
        });
    }
    const rp=document.getElementById('regPassword'), cp=document.getElementById('confirmPassword'), ms=document.getElementById('matchStatus');
    const strength=document.getElementById('strength');
    if(rp&&strength){
        const bars=strength.querySelectorAll('.strength-bar'), label=strength.querySelector('.strength-label');
        rp.addEventListener('input',function(){
            const v=rp.value;
            let score=0;
            if(v.length>=6) score++;
            if(v.length>=10) score++;
            if(/[A-Z]/.test(v)&&/[a-z]/.test(v)) score++;
            if(/\d/.test(v)&&/[^A-Za-z0-9]/.test(v)) score++;
            bars.forEach((b,i)=>b.classList.toggle('active',i<Math.min(score,3)));
            if(!v){label.textContent='Use 6+ characters';}
            else if(v.length<6){label.textContent='Too short — use at least 6 characters';}
            else if(score<=2){label.textContent='Password is acceptable';}
            else{label.textContent='Strong password';}
        });
    }
    function checkMatch(){ if(!rp||!cp||!ms)return; const dot=ms.querySelector('.status-dot'), text=ms.querySelector('span:last-child'); if(!cp.value){dot.className='status-dot';text.textContent='Repeat your password';ms.className='password-status';} else if(rp.value===cp.value&&rp.value.length>=6){dot.className='status-dot valid';text.textContent='Passwords match';ms.className='password-status valid';} else {dot.className='status-dot invalid';text.textContent='Passwords do not match';ms.className='password-status invalid';} }
    rp?.addEventListener('input',checkMatch); cp?.addEventListener('input',checkMatch);
    document.querySelector('[data-switch-register]')?.addEventListener('click',function(e){if(window.parent!==window){e.preventDefault();window.parent.postMessage({type:'auth-switch',mode:'register'},'*');}});
    document.querySelector('[data-switch-login]')?.addEventListener('click',function(e){if(window.parent!==window){e.preventDefault();window.parent.postMessage({type:'auth-switch',mode:'login'},'*');}});
});

// Forgot-password page can live inside the same auth popup.
document.querySelector('[data-forgot-password]')?.addEventListener('click', function(e){
    if(window.parent!==window){
        e.preventDefault();
        window.location.href=this.href;
    }
});


/* Modern login UX */
document.addEventListener('DOMContentLoaded', function(){
    const form = document.querySelector('.login-form');
    const identifier = document.getElementById('identifier');
    if (identifier && !identifier.value) identifier.focus();

    if (form) {
        form.addEventListener('submit', function(){
            const button = form.querySelector('.login-button');
            if (!button || button.dataset.submitting === '1') return;
            button.dataset.submitting = '1';
            button.disabled = true;
            button.setAttribute('aria-busy','true');
            const text = button.querySelector('span');
            if (text) text.textContent = 'Signing in…';
            const icon = button.querySelector('i');
            if (icon) icon.className = 'fa-solid fa-spinner fa-spin';
        });
    }
});
