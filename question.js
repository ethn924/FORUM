document.addEventListener('DOMContentLoaded', function() {
    // Bouton favoris
    const btnFavoris = document.querySelector('.btn-favoris');
    if (btnFavoris) {
        btnFavoris.addEventListener('click', function(e) {
            e.preventDefault();
            const qId = this.getAttribute('data-q-id');
            
            fetch('ajouter_favoris.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `q_id=${qId}&action=toggle`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.is_favoris) {
                        btnFavoris.textContent = '⭐';
                    } else {
                        btnFavoris.textContent = '☆';
                    }
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    }

    const likeButtons = document.querySelectorAll('.like-btn');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const rId = this.getAttribute('data-r-id');
            const isLiked = this.classList.contains('liked');
            const action = isLiked ? 'unlike' : 'like';
            const likesCount = this.querySelector('.likes-count');
            
            fetch('like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&r_id=${rId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    likesCount.textContent = data.total_likes;
                    
                    if (action === 'like') {
                        this.classList.add('liked');
                    } else {
                        this.classList.remove('liked');
                    }
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    });

    const commentButtons = document.querySelectorAll('.btn-commenter');
    
    commentButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const formId = 'form-commentaire-' + this.getAttribute('data-r-id');
            const form = document.getElementById(formId);
            
            if (form) {
                if (form.style.display === 'none' || form.style.display === '') {
                    form.style.display = 'block';
                    form.classList.add('visible');
                    form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    form.style.display = 'none';
                    form.classList.remove('visible');
                }
            }
        });
    });
});
