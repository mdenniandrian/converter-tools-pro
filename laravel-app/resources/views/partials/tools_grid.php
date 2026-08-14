        <!-- 1. TOOLS SELECTION GRID -->
        <div class="grid" id="toolsGrid">
            
            <div class="card" data-category="doc" onclick="selectTool('pdf2word')">
                <div class="card-icon" style="background: rgba(14, 165, 233, 0.15); color: #0ea5e9;">📄</div>
                <h3 class="card-title">PDF to Word</h3>
                <p class="card-desc">Convert PDF files to editable Microsoft Word (.docx) documents accurately.</p>
            </div>

            <div class="card" data-category="doc" onclick="selectTool('pdf2excel')">
                <div class="card-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">📊</div>
                <h3 class="card-title">PDF to Excel</h3>
                <p class="card-desc">Extract tables and spreadsheet data from PDF into Microsoft Excel (.xlsx).</p>
            </div>

            <div class="card" data-category="doc" onclick="selectTool('doc2pdf')">
                <div class="card-icon" style="background: rgba(168, 85, 247, 0.15); color: #a855f7;">📝</div>
                <h3 class="card-title">Word / Excel to PDF</h3>
                <p class="card-desc">Convert Word (.docx) or Excel (.xlsx) documents into clean PDF format.</p>
            </div>

            <div class="card" data-category="img" onclick="selectTool('imgconvert')">
                <div class="card-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">🖼️</div>
                <h3 class="card-title">Image Converter</h3>
                <p class="card-desc">Batch convert images between PNG, JPG, WEBP, and PDF format effortlessly.</p>
            </div>

            <div class="card" data-category="removebg" onclick="selectTool('removebg')">
                <div class="card-icon" style="background: rgba(236, 72, 153, 0.15); color: #ec4899;">✨</div>
                <h3 class="card-title">AI Background Remover</h3>
                <p class="card-desc">Remove image backgrounds automatically with remove.bg style transparent canvas.</p>
            </div>

            <div class="card" data-category="compress" onclick="selectTool('compress')">
                <div class="card-icon" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">🗜️</div>
                <h3 class="card-title">File Compressor</h3>
                <p class="card-desc">Reduce file size of PDF, Word, Excel, and Images up to 80% with zero quality loss.</p>
            </div>

        </div>
