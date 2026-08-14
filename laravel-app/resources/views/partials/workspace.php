        <!-- 2. CONVERSION WORKSPACE VIEW -->
        <div class="workspace-card" id="workspaceCard" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <button class="back-btn" onclick="showToolsGrid()">← Back to Tools</button>
                <h2 style="font-size: 1.3rem; font-weight: 700;" id="workspaceTitle">Tool Title</h2>
            </div>

            <!-- VIEW A: AI REMOVE BACKGROUND (SPLIT PREVIEW CANVAS) -->
            <div id="wsRemoveBg" style="display: none;">
                <div class="dropzone" id="dropzoneRemoveBg" onclick="document.getElementById('inputRemoveBgFile').click()">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">✨</div>
                    <p style="font-weight: 600; margin-bottom: 0.25rem;">Click or Drag & Drop image here</p>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Supports PNG, JPG, WEBP (Max 100MB)</span>
                    <input type="file" id="inputRemoveBgFile" accept="image/*" style="display: none;" onchange="handleRemoveBgFileSelect(this.files)">
                </div>

                <!-- PREVIEW CONTAINER (ORIGINAL vs TRANSPARENT CHECKERBOARD) -->
                <div class="removebg-preview-container" id="removeBgPreviewContainer" style="display: none;">
                    <div class="preview-box">
                        <div class="preview-title">Original Image</div>
                        <img id="imgPreviewOriginal" src="" alt="Original Preview">
                    </div>

                    <div class="preview-box">
                        <div class="preview-title">AI Background Removed Result</div>
                        <div class="transparent-checkerboard" id="resultCanvasBox">
                            <img id="imgPreviewResult" src="" alt="Background Removed Result" style="display: none;">
                            <div class="spinner-loader" id="removeBgSpinner" style="display: none;"></div>
                            <span id="removeBgPlaceholderText" style="color: var(--text-muted); font-size: 0.85rem;">Processing image...</span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; text-align: right;" id="removeBgActionArea" style="display: none;">
                    <button type="button" class="action-btn" id="btnDownloadRemoveBg" style="display: none;" onclick="downloadRemoveBgResult()">⬇️ Download Transparent PNG</button>
                </div>
            </div>

            <!-- VIEW B: BATCH MULTI-FILE CONVERTER -->
            <div id="wsBatch" style="display: none;">
                <form id="batchUploadForm" onsubmit="handleBatchSubmit(event)">
                    <div class="dropzone" id="dropzoneBatch" onclick="document.getElementById('inputBatchFiles').click()">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📁</div>
                        <p style="font-weight: 600; margin-bottom: 0.25rem;">Click or Drag & Drop multiple files here</p>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">Batch conversion supported (Max 100MB per file)</span>
                        <input type="file" id="inputBatchFiles" multiple style="display: none;" onchange="handleBatchFilesSelect(this.files)">
                    </div>

                    <!-- Selected Files Badges with Delete Buttons -->
                    <div class="file-badges-container" id="batchFileBadges"></div>

                    <div id="batchFormatGroup">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Target Format</label>
                        <select class="select-input" id="batchTargetFormat">
                            <option value="docx">Word (.docx)</option>
                            <option value="xlsx">Excel (.xlsx)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                    </div>
                    <button type="submit" class="action-btn" id="batchSubmitBtn">Start Conversion</button>
                </form>

                <div style="margin-top: 2rem;" id="batchResultsArea">
                    <div id="batchJobCards"></div>
                </div>
            </div>

        </div>
