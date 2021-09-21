import { extend, override } from 'flarum/common/extend';
import LogInModal from 'flarum/forum/components/LogInModal';
import Button from 'flarum/common/components/Button';

function post(path, params, method='post') {
  const form = document.createElement('form');
  form.method = method;
  form.action = path;
  form.target = '_blank';

  for (const key in params) {
    if (params.hasOwnProperty(key)) {
      const hiddenField = document.createElement('input');
      hiddenField.type = 'hidden';
      hiddenField.name = key;
      hiddenField.value = params[key];

      form.appendChild(hiddenField);
    }
  }

  document.body.appendChild(form);
  form.submit();
}


app.initializers.add('cccRaim/flarum-jh-login', () => {

  LogInModal.prototype.onSubmit = function onSubmit (e) {
    e.preventDefault();
    this.loading = true;

    const identification = this.identification();
    const password = this.password();
    const remember = this.remember();

    post('/auth/jh', {
      identification,
      password,
      remember,
      csrfToken: window.flarum.core.app.session.csrfToken,
    });
  };

  extend(LogInModal.prototype, 'fields', function (items) {
    items.replace(
      'identification',
      <div className="Form-group">
        <input
          className="FormControl"
          name="identification"
          type="text"
          placeholder="请输入精弘通行证(学号)"
          bidi={this.identification}
          disabled={this.loading}
        />
      </div>,
      30
    );

    items.replace(
      'submit',
      <div className="Form-group">
        {Button.component(
          {
            className: 'Button Button--primary Button--block',
            onclick: this.onSubmit.bind(this),
            loading: this.loading,
          },
          app.translator.trans('core.forum.log_in.submit_button')
        )}
      </div>,
      -10
    );
  });

  override(LogInModal.prototype, 'footer', function () {
    return [
      <p className="LogInModal-forgotPassword">
        <a href="http://user.jh.zjut.edu.cn/index.php?app=passport&action=applyResetPwd" target="_blank">
          忘记密码？
        </a>
      </p>,

      <p className="LogInModal-signUp">还没有精弘通行证？马上<a href="http://user.jh.zjut.edu.cn/index.php?app=passport&action=active" target="_blank">激活通行证</a></p>
    ];
  });


});
