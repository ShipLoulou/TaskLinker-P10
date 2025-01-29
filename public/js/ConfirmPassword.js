class ConfirmPassword {
    constructor() {
        this.form = document.getElementById('formRegisterJS');
        this.container = document.getElementById('confirmPasswordJS');
        this.input = document.getElementById('registration_form_confirmPassword');

        this.validation();
    }

    validation() {

        if (this.form) {
            this.form.addEventListener("submit", event => {
                let password = document.getElementById('registration_form_plainPassword');
                let confim = document.getElementById('registration_form_confirmPassword');

                password = password.value;
                confim = confim.value;

                if (password !== confim) {
                    event.preventDefault();
                    const ul = document.createElement('ul');
                    ul.classList.add('messageError');
                    ul.innerHTML = `<li>Les mots de passe sont différents</li>`;
                    this.container.insertBefore(ul, this.input);
                }
            })
        }
    }
}

new ConfirmPassword;