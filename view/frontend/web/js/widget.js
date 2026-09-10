define(['mage/translate'], function ($t) {
    'use strict';

    return function (config, element) {
        var launcher = element.querySelector('.vera-searchai__launcher');
        var panel = element.querySelector('.vera-searchai__panel');
        var closeButton = element.querySelector('.vera-searchai__close');
        var title = element.querySelector('.vera-searchai__title');
        var messages = element.querySelector('.vera-searchai__messages');
        var status = element.querySelector('.vera-searchai__status');
        var form = element.querySelector('.vera-searchai__form');
        var input = element.querySelector('.vera-searchai__input');
        var sendButton = element.querySelector('.vera-searchai__send');
        var history = loadHistory();
        var sending = false;
        var lastMessage = '';

        title.textContent = config.assistantName;
        input.placeholder = config.inputPlaceholder;
        addMessage('assistant', config.welcomeMessage, []);
        history.forEach(function (item) {
            addMessage(item.role, item.content, []);
        });

        launcher.addEventListener('click', open);
        closeButton.addEventListener('click', close);
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            submit(input.value);
        });
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                submit(input.value);
            } else if (event.key === 'Escape') {
                close();
            }
        });
        panel.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                close();
            }
        });

        function open() {
            panel.hidden = false;
            launcher.setAttribute('aria-expanded', 'true');
            input.focus();
        }

        function close() {
            panel.hidden = true;
            launcher.setAttribute('aria-expanded', 'false');
            launcher.focus();
        }

        function submit(rawMessage, isRetry) {
            var message = String(rawMessage || '').trim();
            if (!message || sending || message.length > config.maxQueryLength) {
                return;
            }
            lastMessage = message;
            sending = true;
            setBusy(true);
            if (!isRetry) {
                addMessage('user', message, []);
            }
            input.value = '';

            var requestHistory = history.slice(-(config.maxHistory || 6));
            var body = new URLSearchParams();
            body.set('form_key', config.formKey);
            body.set('message', message);
            body.set('history', JSON.stringify(requestHistory));

            fetch(config.endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: body.toString()
            }).then(function (response) {
                return response.json().then(function (data) {
                    return {ok: response.ok, data: data};
                });
            }).then(function (result) {
                if (!result.ok || !result.data || result.data.success !== true) {
                    throw result.data || {};
                }
                remember('user', message);
                remember('assistant', String(result.data.answer || ''));
                addMessage('assistant', String(result.data.answer || ''), result.data.products || []);
                status.textContent = '';
            }).catch(function (error) {
                var messageText = error && error.error_code === 'rate_limited'
                    ? config.labels.rateLimited
                    : config.labels.unavailable;
                showError(messageText);
            }).finally(function () {
                sending = false;
                setBusy(false);
                input.focus();
            });
        }

        function addMessage(role, text, products) {
            var wrapper = document.createElement('div');
            wrapper.className = 'vera-searchai__message vera-searchai__message--' + role;
            var copy = document.createElement('p');
            copy.textContent = String(text || '');
            wrapper.appendChild(copy);

            if (Array.isArray(products)) {
                products.forEach(function (product) {
                    var card = createCard(product);
                    if (card) {
                        wrapper.appendChild(card);
                    }
                });
            }
            messages.appendChild(wrapper);
            messages.scrollTop = messages.scrollHeight;
        }

        function createCard(product) {
            var url = safeUrl(product.url);
            if (!url || !product || typeof product.name !== 'string') {
                return null;
            }
            var card = document.createElement('article');
            card.className = 'vera-searchai__product';
            var imageUrl = safeUrl(product.image);
            if (imageUrl) {
                var image = document.createElement('img');
                image.src = imageUrl;
                image.alt = '';
                image.loading = 'lazy';
                card.appendChild(image);
            }
            var content = document.createElement('div');
            var name = document.createElement('h3');
            name.textContent = product.name;
            content.appendChild(name);
            var price = document.createElement('p');
            price.className = 'vera-searchai__price';
            price.textContent = String(product.price || '');
            content.appendChild(price);
            var link = document.createElement('a');
            link.href = url;
            link.textContent = $t('View product');
            content.appendChild(link);
            card.appendChild(content);
            return card;
        }

        function safeUrl(value) {
            if (typeof value !== 'string' || value === '') {
                return '';
            }
            try {
                var parsed = new URL(value, window.location.origin);
                return parsed.protocol === 'http:' || parsed.protocol === 'https:' ? parsed.href : '';
            } catch (error) {
                return '';
            }
        }

        function showError(text) {
            status.textContent = '';
            var copy = document.createElement('span');
            copy.textContent = text;
            var retry = document.createElement('button');
            retry.type = 'button';
            retry.className = 'vera-searchai__retry';
            retry.textContent = config.labels.retry;
            retry.addEventListener('click', function () {
                status.textContent = '';
                submit(lastMessage, true);
            });
            status.appendChild(copy);
            status.appendChild(retry);
        }

        function setBusy(value) {
            sendButton.disabled = value;
            input.disabled = value;
            element.classList.toggle('vera-searchai--sending', value);
            status.textContent = value ? $t('Searching the catalog…') : '';
        }

        function remember(role, content) {
            if (!content) {
                return;
            }
            history.push({role: role, content: content});
            history = history.slice(-(config.maxHistory || 6));
            try {
                window.sessionStorage.setItem(config.storageKey, JSON.stringify(history));
            } catch (error) {
                history = history.slice(-(config.maxHistory || 6));
            }
        }

        function loadHistory() {
            try {
                var stored = JSON.parse(window.sessionStorage.getItem(config.storageKey) || '[]');
                if (!Array.isArray(stored)) {
                    return [];
                }
                return stored.filter(function (item) {
                    return item && (item.role === 'user' || item.role === 'assistant')
                        && typeof item.content === 'string';
                }).slice(-(config.maxHistory || 6));
            } catch (error) {
                return [];
            }
        }
    };
});
