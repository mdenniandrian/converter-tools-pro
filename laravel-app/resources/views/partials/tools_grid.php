        <!-- 1. TOOLS SELECTION GRID -->
        <div class="grid" id="toolsGrid">
            
            <div class="card" data-category="doc" onclick="selectTool('pdf2word')">
                <div class="card-icon" style="background: rgba(6, 182, 212, 0.12); border: 1px solid rgba(6, 182, 212, 0.25);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#06b6d4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                    <h3 class="card-title">PDF to Word</h3>
                    <span style="font-size: 0.7rem; font-weight: 700; color: #06b6d4; background: rgba(6, 182, 212, 0.12); padding: 0.15rem 0.5rem; border-radius: 8px;">FREE</span>
                </div>
                <p class="card-desc">Convert PDF documents into fully editable Microsoft Word (.docx) files smoothly.</p>
            </div>

            <div class="card" data-category="doc" onclick="selectTool('pdf2excel')">
                <div class="card-icon" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                    <h3 class="card-title">PDF to Excel</h3>
                    <span style="font-size: 0.7rem; font-weight: 700; color: #10b981; background: rgba(16, 185, 129, 0.12); padding: 0.15rem 0.5rem; border-radius: 8px;">FREE</span>
                </div>
                <p class="card-desc">Extract tabular data and spreadsheets from PDF into structured Excel (.xlsx) workbooks.</p>
            </div>

            <div class="card" data-category="doc" onclick="selectTool('doc2pdf')">
                <div class="card-icon" style="background: rgba(168, 85, 247, 0.12); border: 1px solid rgba(168, 85, 247, 0.25);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                    <h3 class="card-title">Word / Excel to PDF</h3>
                    <span style="font-size: 0.7rem; font-weight: 700; color: #a855f7; background: rgba(168, 85, 247, 0.12); padding: 0.15rem 0.5rem; border-radius: 8px;">FREE</span>
                </div>
                <p class="card-desc">Convert Word documents or Excel spreadsheets into universal print-ready PDF format.</p>
            </div>

            <div class="card" data-category="img" onclick="selectTool('imgconvert')">
                <div class="card-icon" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.25);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                    <h3 class="card-title">Image Converter</h3>
                    <span style="font-size: 0.7rem; font-weight: 700; color: #f59e0b; background: rgba(245, 158, 11, 0.12); padding: 0.15rem 0.5rem; border-radius: 8px;">PRO</span>
                </div>
                <p class="card-desc">Batch convert images between PNG, JPG, WEBP, and PDF formats with high fidelity.</p>
            </div>

            <div class="card" data-category="removebg" onclick="selectTool('removebg')">
                <div class="card-icon" style="background: rgba(236, 72, 153, 0.12); border: 1px solid rgba(236, 72, 153, 0.25);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                    <h3 class="card-title">AI Background Remover</h3>
                    <span style="font-size: 0.7rem; font-weight: 700; color: #ec4899; background: rgba(236, 72, 153, 0.12); padding: 0.15rem 0.5rem; border-radius: 8px;">ENTERPRISE</span>
                </div>
                <p class="card-desc">Automated AI edge detection to erase photo backgrounds instantly into transparent PNGs.</p>
            </div>

            <div class="card" data-category="compress" onclick="selectTool('compress')">
                <div class="card-icon" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 14 10 14 10 20"/><polyline points="20 10 14 10 14 4"/><line x1="14" y1="10" x2="21" y2="3"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                    <h3 class="card-title">File Compressor</h3>
                    <span style="font-size: 0.7rem; font-weight: 700; color: #ef4444; background: rgba(239, 68, 68, 0.12); padding: 0.15rem 0.5rem; border-radius: 8px;">PRO</span>
                </div>
                <p class="card-desc">Shrink PDF documents and images up to 80% file size reduction while preserving crisp text.</p>
            </div>

        </div>
