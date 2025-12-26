<section class="royal-messages section-padding" id="messages">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Ucapan & Doa</h2>
            <div class="divider"></div>
            <p class="section-subtitle">Tuliskan ucapan dan doa untuk kedua mempelai</p>
        </div>
        
        <div class="messages-container">
            <!-- Message Form -->
            <div class="message-form-container">
                <form id="messageForm" class="message-form">
                    <div class="form-header">
                        <h3><i class="fas fa-edit"></i> Tulis Ucapan</h3>
                        <p>Ucapan Anda akan ditampilkan di halaman ini</p>
                    </div>
                    
                    <div class="form-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="msg_name">Nama Anda *</label>
                                <input type="text" 
                                       id="msg_name" 
                                       name="name" 
                                       required
                                       placeholder="Nama lengkap atau inisial">
                            </div>
                            <div class="form-group">
                                <label for="msg_relation">Hubungan dengan Mempelai</label>
                                <select id="msg_relation" name="relation">
                                    <option value="">Pilih hubungan</option>
                                    <option value="family">Keluarga</option>
                                    <option value="friend">Teman</option>
                                    <option value="colleague">Rekan Kerja</option>
                                    <option value="relative">Kerabat</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="msg_message">Ucapan & Doa *</label>
                            <textarea id="msg_message" 
                                      name="message" 
                                      rows="4"
                                      required
                                      maxlength="500"
                                      placeholder="Tulis ucapan dan doa terbaik untuk mempelai..."></textarea>
                            <div class="char-counter">
                                <span id="charCount">0</span>/500 karakter
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Kirim secara:</label>
                            <div class="privacy-options">
                                <label class="privacy-option">
                                    <input type="radio" 
                                           name="privacy" 
                                           value="public" 
                                           checked>
                                    <div class="option-content">
                                        <i class="fas fa-globe"></i>
                                        <span>Publik</span>
                                        <small>Tampilkan di halaman ini</small>
                                    </div>
                                </label>
                                <label class="privacy-option">
                                    <input type="radio" 
                                           name="privacy" 
                                           value="private">
                                    <div class="option-content">
                                        <i class="fas fa-lock"></i>
                                        <span>Pribadi</span>
                                        <small>Hanya untuk mempelai</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-submit" id="submitMessage">
                                <i class="fas fa-paper-plane"></i>
                                <span class="btn-text">Kirim Ucapan</span>
                                <div class="spinner hidden" id="messageSpinner"></div>
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Message Preview -->
                <div class="message-preview">
                    <h4><i class="fas fa-eye"></i> Pratinjau Ucapan</h4>
                    <div class="preview-card">
                        <div class="preview-avatar" id="previewAvatar">J</div>
                        <div class="preview-content">
                            <div class="preview-header">
                                <h5 id="previewName">Nama Anda</h5>
                                <span class="preview-relation" id="previewRelation">Teman</span>
                                <span class="preview-time">Baru saja</span>
                            </div>
                            <p class="preview-message" id="previewMessage">Ucapan Anda akan muncul di sini...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Messages List -->
            <div class="messages-list-container">
                <div class="messages-header">
                    <h3><i class="fas fa-comments"></i> Ucapan Tamu</h3>
                    <div class="messages-filter">
                        <button class="filter-btn active" data-filter="all">Semua</button>
                        <button class="filter-btn" data-filter="family">Keluarga</button>
                        <button class="filter-btn" data-filter="friend">Teman</button>
                        <button class="filter-btn" data-filter="colleague">Rekan Kerja</button>
                    </div>
                </div>
                
                <div class="messages-list" id="messagesList">
                    <!-- Messages will be loaded here via AJAX -->
                </div>
                
                <div class="messages-loading" id="messagesLoading">
                    <div class="spinner"></div>
                    <p>Memuat ucapan...</p>
                </div>
                
                <div class="messages-empty" id="messagesEmpty" style="display: none;">
                    <i class="fas fa-comment-slash"></i>
                    <p>Belum ada ucapan. Jadilah yang pertama!</p>
                </div>
                
                <button class="btn-load-more" id="loadMoreMessages" style="display: none;">
                    <span>Muat Lebih Banyak</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        
        <!-- Message Statistics -->
        <div class="message-stats">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-comment"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="totalMessages">0</div>
                    <div class="stat-label">Total Ucapan</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="uniqueSenders">0</div>
                    <div class="stat-label">Pengirim</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="messageLikes">0</div>
                    <div class="stat-label">Suka</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Message Detail Modal -->
    <div class="modal" id="messageDetailModal">
        <div class="modal-content message-detail">
            <button class="modal-close" onclick="closeMessageDetail()">&times;</button>
            
            <div class="message-detail-header">
                <div class="detail-avatar" id="detailAvatar">J</div>
                <div class="detail-info">
                    <h3 id="detailName">Nama Pengirim</h3>
                    <div class="detail-meta">
                        <span class="detail-relation" id="detailRelation">Teman</span>
                        <span class="detail-time" id="detailTime">2 jam yang lalu</span>
                    </div>
                </div>
            </div>
            
            <div class="message-detail-body">
                <p id="detailMessage">Isi pesan...</p>
            </div>
            
            <div class="message-detail-actions">
                <button class="btn-like" onclick="likeMessage()" id="likeButton">
                    <i class="far fa-heart"></i>
                    <span id="likeCount">0</span>
                </button>
                <button class="btn-reply" onclick="replyToMessage()">
                    <i class="fas fa-reply"></i> Balas
                </button>
                <button class="btn-share" onclick="shareMessage()">
                    <i class="fas fa-share-alt"></i> Bagikan
                </button>
            </div>
            
            <!-- Replies Section -->
            <div class="message-replies">
                <h4>Balasan</h4>
                <div class="replies-list" id="repliesList">
                    <!-- Replies will be loaded here -->
                </div>
                <form class="reply-form" id="replyForm" style="display: none;">
                    <textarea placeholder="Tulis balasan..." rows="2" id="replyText"></textarea>
                    <button type="submit" class="btn-send-reply">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
// Global variables
let currentMessagePage = 1;
let isMessagesLoading = false;
let hasMoreMessages = true;
let currentFilter = 'all';
let currentDetailMessageId = null;

// Character counter for message textarea
document.getElementById('msg_message').addEventListener('input', function() {
    const charCount = this.value.length;
    document.getElementById('charCount').textContent = charCount;
    
    // Update preview
    document.getElementById('previewMessage').textContent = 
        this.value || 'Ucapan Anda akan muncul di sini...';
});

// Update preview when name changes
document.getElementById('msg_name').addEventListener('input', function() {
    const name = this.value.trim();
    document.getElementById('previewName').textContent = name || 'Nama Anda';
    document.getElementById('previewAvatar').textContent = 
        name ? name.charAt(0).toUpperCase() : 'J';
});

// Update preview when relation changes
document.getElementById('msg_relation').addEventListener('change', function() {
    const relation = this.value;
    const relationText = getRelationText(relation);
    document.getElementById('previewRelation').textContent = relationText;
});

function getRelationText(relation) {
    const relations = {
        'family': 'Keluarga',
        'friend': 'Teman',
        'colleague': 'Rekan Kerja',
        'relative': 'Kerabat',
        'other': 'Tamu'
    };
    return relations[relation] || 'Tamu';
}

// Load messages
async function loadMessages(page = 1, filter = 'all') {
    if(isMessagesLoading) return;
    
    isMessagesLoading = true;
    showLoading(true);
    
    try {
        const response = await fetch(
            `<?= base_url("api/messages/list?page=") ?>${page}&filter=${filter}`
        );
        const data = await response.json();
        
        if(data.success) {
            const messagesList = document.getElementById('messagesList');
            const messagesEmpty = document.getElementById('messagesEmpty');
            const loadMoreBtn = document.getElementById('loadMoreMessages');
            
            if(page === 1) {
                messagesList.innerHTML = '';
            }
            
            if(data.data.messages.length === 0 && page === 1) {
                messagesEmpty.style.display = 'block';
                loadMoreBtn.style.display = 'none';
            } else {
                messagesEmpty.style.display = 'none';
                
                data.data.messages.forEach(message => {
                    const messageElement = createMessageElement(message);
                    messagesList.appendChild(messageElement);
                });
                
                // Check if there are more messages
                hasMoreMessages = data.data.has_more;
                if(hasMoreMessages) {
                    loadMoreBtn.style.display = 'flex';
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            }
            
            // Update stats
            updateStats(data.data.stats);
            
            currentMessagePage = page;
        }
    } catch(error) {
        console.error('Error loading messages:', error);
        showNotification('Gagal memuat ucapan', 'error');
    } finally {
        isMessagesLoading = false;
        showLoading(false);
    }
}

function createMessageElement(message) {
    const element = document.createElement('div');
    element.className = 'message-item';
    element.dataset.id = message.id;
    element.dataset.relation = message.relation;
    
    const timeAgo = formatTimeAgo(message.created_at);
    const relationText = getRelationText(message.relation);
    
    element.innerHTML = `
        <div class="message-avatar">
            ${message.name.charAt(0).toUpperCase()}
        </div>
        <div class="message-content">
            <div class="message-header">
                <h4 class="message-sender">${message.name}</h4>
                <span class="message-relation">${relationText}</span>
                <span class="message-time">${timeAgo}</span>
            </div>
            <p class="message-text">${message.message}</p>
            <div class="message-footer">
                <button class="btn-like-message" onclick="toggleLike(${message.id}, this)">
                    <i class="${message.is_liked ? 'fas' : 'far'} fa-heart"></i>
                    <span class="like-count">${message.like_count || 0}</span>
                </button>
                <button class="btn-reply-message" onclick="showReplyForm(${message.id})">
                    <i class="fas fa-reply"></i> Balas
                </button>
                <button class="btn-view-message" onclick="viewMessageDetail(${message.id})">
                    <i class="fas fa-expand-alt"></i> Detail
                </button>
            </div>
        </div>
    `;
    
    return element;
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if(diffMins < 1) {
        return 'Baru saja';
    } else if(diffMins < 60) {
        return `${diffMins} menit yang lalu`;
    } else if(diffHours < 24) {
        return `${diffHours} jam yang lalu`;
    } else if(diffDays < 7) {
        return `${diffDays} hari yang lalu`;
    } else {
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }
}

function updateStats(stats) {
    document.getElementById('totalMessages').textContent = stats.total;
    document.getElementById('uniqueSenders').textContent = stats.senders;
    document.getElementById('messageLikes').textContent = stats.likes;
}

function showLoading(show) {
    document.getElementById('messagesLoading').style.display = 
        show ? 'block' : 'none';
}

// Submit message form
document.getElementById('messageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitMessage');
    const spinner = document.getElementById('messageSpinner');
    const btnText = submitBtn.querySelector('.btn-text');
    
    // Validate
    const message = document.getElementById('msg_message').value.trim();
    if(message.length < 5) {
        showNotification('Ucapan minimal 5 karakter', 'error');
        return;
    }
    
    // Show loading
    submitBtn.disabled = true;
    spinner.classList.remove('hidden');
    btnText.textContent = 'Mengirim...';
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('<?= base_url("api/messages/submit") ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if(data.success) {
            // Show success
            showNotification('Ucapan berhasil dikirim!', 'success');
            
            // Reset form
            this.reset();
            document.getElementById('charCount').textContent = '0';
            document.getElementById('previewMessage').textContent = 'Ucapan Anda akan muncul di sini...';
            document.getElementById('previewName').textContent = 'Nama Anda';
            document.getElementById('previewAvatar').textContent = 'J';
            document.getElementById('previewRelation').textContent = 'Teman';
            
            // Reload messages
            loadMessages(1, currentFilter);
        } else {
            showNotification(data.message || 'Gagal mengirim ucapan', 'error');
        }
    } catch(error) {
        console.error('Error submitting message:', error);
        showNotification('Gagal mengirim ucapan', 'error');
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        spinner.classList.add('hidden');
        btnText.textContent = 'Kirim Ucapan';
    }
});

// Filter messages
document.querySelectorAll('.messages-filter .filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.dataset.filter;
        
        // Update active button
        document.querySelectorAll('.messages-filter .filter-btn').forEach(b => {
            b.classList.remove('active');
        });
        this.classList.add('active');
        
        // Load messages with filter
        currentFilter = filter;
        loadMessages(1, filter);
    });
});

// Load more messages
document.getElementById('loadMoreMessages').addEventListener('click', function() {
    if(!isMessagesLoading && hasMoreMessages) {
        loadMessages(currentMessagePage + 1, currentFilter);
    }
});

// Like message
async function toggleLike(messageId, button) {
    try {
        const response = await fetch('<?= base_url("api/messages/like") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message_id: messageId })
        });
        
        const data = await response.json();
        
        if(data.success) {
            const likeIcon = button.querySelector('i');
            const likeCount = button.querySelector('.like-count');
            
            if(data.data.liked) {
                likeIcon.className = 'fas fa-heart';
                likeCount.textContent = parseInt(likeCount.textContent) + 1;
                showNotification('Ucapan disukai', 'success');
            } else {
                likeIcon.className = 'far fa-heart';
                likeCount.textContent = parseInt(likeCount.textContent) - 1;
            }
            
            // Update total likes in stats
            const currentLikes = parseInt(document.getElementById('messageLikes').textContent);
            document.getElementById('messageLikes').textContent = 
                data.data.liked ? currentLikes + 1 : currentLikes - 1;
        }
    } catch(error) {
        console.error('Error toggling like:', error);
    }
}

// View message detail
async function viewMessageDetail(messageId) {
    currentDetailMessageId = messageId;
    
    try {
        const response = await fetch(`<?= base_url("api/messages/detail/") ?>${messageId}`);
        const data = await response.json();
        
        if(data.success) {
            const message = data.data;
            
            // Update modal content
            document.getElementById('detailName').textContent = message.name;
            document.getElementById('detailAvatar').textContent = message.name.charAt(0).toUpperCase();
            document.getElementById('detailRelation').textContent = getRelationText(message.relation);
            document.getElementById('detailTime').textContent = formatTimeAgo(message.created_at);
            document.getElementById('detailMessage').textContent = message.message;
            document.getElementById('likeCount').textContent = message.like_count || 0;
            
            // Update like button
            const likeButton = document.getElementById('likeButton');
            const likeIcon = likeButton.querySelector('i');
            likeIcon.className = message.is_liked ? 'fas fa-heart' : 'far fa-heart';
            
            // Load replies
            loadReplies(messageId);
            
            // Show modal
            document.getElementById('messageDetailModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    } catch(error) {
        console.error('Error loading message detail:', error);
        showNotification('Gagal memuat detail ucapan', 'error');
    }
}

function closeMessageDetail() {
    document.getElementById('messageDetailModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

async function loadReplies(messageId) {
    try {
        const response = await fetch(`<?= base_url("api/messages/replies/") ?>${messageId}`);
        const data = await response.json();
        
        if(data.success) {
            const repliesList = document.getElementById('repliesList');
            repliesList.innerHTML = '';
            
            if(data.data.replies.length === 0) {
                repliesList.innerHTML = '<p class="no-replies">Belum ada balasan</p>';
            } else {
                data.data.replies.forEach(reply => {
                    const replyElement = document.createElement('div');
                    replyElement.className = 'reply-item';
                    replyElement.innerHTML = `
                        <div class="reply-avatar">
                            ${reply.name.charAt(0).toUpperCase()}
                        </div>
                        <div class="reply-content">
                            <div class="reply-header">
                                <strong>${reply.name}</strong>
                                <span class="reply-time">${formatTimeAgo(reply.created_at)}</span>
                            </div>
                            <p>${reply.message}</p>
                        </div>
                    `;
                    repliesList.appendChild(replyElement);
                });
            }
            
            // Show reply form
            document.getElementById('replyForm').style.display = 'flex';
        }
    } catch(error) {
        console.error('Error loading replies:', error);
    }
}

// Reply to message
document.getElementById('replyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const replyText = document.getElementById('replyText').value.trim();
    if(!replyText) return;
    
    try {
        const response = await fetch('<?= base_url("api/messages/reply") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message_id: currentDetailMessageId,
                message: replyText
            })
        });
        
        const data = await response.json();
        
        if(data.success) {
            // Clear input
            document.getElementById('replyText').value = '';
            
            // Reload replies
            loadReplies(currentDetailMessageId);
            
            showNotification('Balasan berhasil dikirim', 'success');
        }
    } catch(error) {
        console.error('Error sending reply:', error);
        showNotification('Gagal mengirim balasan', 'error');
    }
});

function showReplyForm(messageId) {
    currentDetailMessageId = messageId;
    document.getElementById('replyText').focus();
}

function likeMessage() {
    const likeButton = document.getElementById('likeButton');
    toggleLike(currentDetailMessageId, likeButton);
}

function shareMessage() {
    const message = document.getElementById('detailMessage').textContent;
    const sender = document.getElementById('detailName').textContent;
    
    if(navigator.share) {
        navigator.share({
            title: `Ucapan dari ${sender}`,
            text: message,
            url: window.location.href,
        });
    } else {
        const shareText = `${sender}: "${message}"`;
        navigator.clipboard.writeText(shareText).then(() => {
            showNotification('Ucapan berhasil disalin', 'success');
        });
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadMessages(1, 'all');
    
    // Close modal on outside click
    document.getElementById('messageDetailModal').addEventListener('click', function(e) {
        if(e.target === this) {
            closeMessageDetail();
        }
    });
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape' && document.getElementById('messageDetailModal').classList.contains('active')) {
            closeMessageDetail();
        }
    });
    
    // Auto-refresh messages every 30 seconds
    setInterval(() => {
        if(!document.getElementById('messageDetailModal').classList.contains('active')) {
            loadMessages(1, currentFilter);
        }
    }, 30000);
});

// Same showNotification function as before
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>
