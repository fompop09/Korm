<!-- footer.php -->
<style>
.footer {
    margin-top: auto;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    color: rgba(255, 255, 255, 0.9);
    padding: 2rem 0;
    position: relative;
    overflow: hidden;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(to right, 
        transparent, 
        rgba(255, 255, 255, 0.2), 
        transparent
    );
}

.footer-content {
    position: relative;
    z-index: 1;
}

.footer-wave {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.1;
    background: linear-gradient(45deg, 
        transparent 45%, 
        rgba(255, 255, 255, 0.1) 50%, 
        transparent 55%
    );
    background-size: 20px 20px;
}

.developer-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.footer-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.footer-item:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.footer-link {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: all 0.3s ease;
}

.footer-link:hover {
    color: white;
}

.version-info {
    text-align: center;
    margin-top: 1rem;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
}

@media (max-width: 768px) {
    .developer-info {
        flex-direction: column;
        gap: 0.5rem;
    }

    .footer-item {
        width: 100%;
        justify-content: center;
    }
}
</style>

<footer class="footer">
    <div class="footer-wave"></div>
    <div class="container footer-content">
        <div class="developer-info">
            <div class="footer-item">
                <i class="bi bi-code-slash"></i>
                <span>พัฒนาโดย Devtaiban</span>
            </div>
            <div class="footer-item">
                <i class="bi bi-globe"></i>
                <a href="http://www.Kruwirat.com" target="_blank" class="footer-link">
                    www.Kruwirat.com
                </a>
            </div>
            <div class="footer-item">
                <i class="bi bi-telephone"></i>
                <a href="tel:0956029737" class="footer-link">095-602-9737</a>
            </div>
        </div>
        <div class="version-info">
            ระบบบริหารจัดการข้อมูลบุคลากร v1.0.0 
            <i class="bi bi-heart-fill text-danger mx-1"></i> 
            ©2025 All rights reserved
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>