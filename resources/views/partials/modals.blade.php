<!-- New Record Modal -->
<div id="new-record-modal" class="modal-overlay">
    <div class="modal-card">
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: var(--color-primary-subtle); color: var(--color-primary); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
                </div>
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Create New Record</h3>
            </div>
            <button type="button" onclick="closeModal('new-record-modal')" style="background: none; border: none; cursor: pointer; color: var(--zinc-400);">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <form onsubmit="event.preventDefault(); alert('Record created successfully (Static Preview)!'); closeModal('new-record-modal');" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Module Type</label>
                <select id="new-record-module-select" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem; background: #ffffff;">
                    <option value="leads">Lead (Inbound Prospect)</option>
                    <option value="accounts">Account (Customer / Vendor)</option>
                    <option value="contacts">Contact (Stakeholder)</option>
                    <option value="opportunities">Opportunity (Sales Pipeline)</option>
                    <option value="tasks">Task (Activity)</option>
                    <option value="purchase_deals">Purchase Deal (Procurement PO)</option>
                    <option value="shipping">Shipment (Freight Logistics)</option>
                    <option value="payments">Invoice / Payment (AR/AP)</option>
                    <option value="wires">Wire Received (EFT / Bank)</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Record Name / Subject *</label>
                    <input type="text" placeholder="e.g. Enterprise Cloud License" required style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Company / Account</label>
                    <input type="text" placeholder="e.g. Skyline Cloud Systems" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Amount / Budget ($ USD)</label>
                    <input type="text" placeholder="e.g. $125,000" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Assigned Rep / Owner</label>
                    <select style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem; background: #ffffff;">
                        <option>Alex Morgan (Executive)</option>
                        <option>Jordan Lee (Sales Rep)</option>
                        <option>Marcus Brody (Procurement)</option>
                        <option>Sarah Chen (Finance Lead)</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Notes &amp; Qualification Details</label>
                <textarea rows="3" placeholder="Enter record notes, terms, or timeline background..." style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem; resize: vertical;"></textarea>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem; padding-top: 1rem; border-top: 1px solid var(--zinc-100);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('new-record-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save &amp; Create Record</button>
            </div>
        </form>
    </div>
</div>

<!-- Record Detail Drawer Modal (Static Preview) -->
<div id="record-detail-modal" class="modal-overlay">
    <div class="modal-card" style="max-width: 840px;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--zinc-200); display: flex; align-items: center; justify-content: space-between; background: #fbfcfd;">
            <div>
                <span class="badge badge-indigo" style="font-size: 0.7rem; margin-bottom: 0.25rem;">Opportunity Record</span>
                <h2 id="modal-detail-title" style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-900); margin: 0;">
                    Skyline Cloud — Phase 2 Compute Cluster
                </h2>
                <span style="font-size: 0.75rem; color: var(--zinc-500);">Account: Skyline Cloud Systems LLC • Owner: Alex Morgan</span>
            </div>
            <button type="button" onclick="closeModal('record-detail-modal')" style="background: none; border: none; cursor: pointer; color: var(--zinc-400);">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>

        <div style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <div style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 600;">AMOUNT</div>
                    <div style="font-size: 1.15rem; font-weight: 800; color: var(--zinc-900);">$145,000</div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 600;">STAGE</div>
                    <div><span class="badge badge-amber">Negotiation</span></div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 600;">COUPLED PO</div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--color-primary);">PO-882194</div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 600;">WIN PROBABILITY</div>
                    <div style="font-size: 1.15rem; font-weight: 800; color: #059669;">75%</div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--zinc-900); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="layers" style="width: 15px; height: 15px;"></i>
                    Salesforce Lightning Activity &amp; Next Steps
                </h4>
                <div style="padding: 0.75rem 1rem; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; font-size: 0.8125rem; color: #065f46;">
                    ✓ <strong>Recent Update:</strong> Hardware inventory allocated from PO-882194 (Batch 48 FPGA Accelerators). Legal contract reviewed by Sarah Jenkins. Closing scheduled within 15 days.
                </div>
            </div>

            <div>
                <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--zinc-900); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="git-pull-request" style="width: 15px; height: 15px;"></i>
                    Linked Records 360° Graph
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.8rem;">
                    <div style="padding: 0.75rem; border: 1px solid var(--zinc-200); border-radius: 8px; background: #ffffff;">
                        <span style="color: var(--zinc-500); font-size: 0.7rem; display: block;">PURCHASE DEAL</span>
                        <strong style="color: var(--zinc-900);">Batch 48 FPGA Accelerators</strong> ($54,000)
                    </div>
                    <div style="padding: 0.75rem; border: 1px solid var(--zinc-200); border-radius: 8px; background: #ffffff;">
                        <span style="color: var(--zinc-500); font-size: 0.7rem; display: block;">INVOICE GENERATED</span>
                        <strong style="color: var(--zinc-900);">INV-2026-0041</strong> (Partially Paid: $30k)
                    </div>
                </div>
            </div>
        </div>

        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--zinc-200); display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; background: #fbfcfd;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('record-detail-modal')">Close</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="alert('Stage advanced to Closed Won!'); closeModal('record-detail-modal');">Mark as Closed Won</button>
        </div>
    </div>
</div>

<!-- Ambient Theme & Glow Studio Modal -->
<div id="theme-palette-modal" class="modal-overlay">
    <div class="modal-card" style="max-width: 900px; max-height: 90vh; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 1.15rem 1.75rem; border-bottom: 1px solid var(--zinc-200); display: flex; align-items: center; justify-content: space-between; background: var(--zinc-50); flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.35);">
                    <i data-lucide="sparkles" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--zinc-900); margin: 0; letter-spacing: -0.02em;">
                            Ambient Theme &amp; Background Studio
                        </h3>
                        <span class="badge badge-amber" style="font-size: 0.675rem; font-weight: 700; text-transform: uppercase;">
                            Personalized Canvas
                        </span>
                    </div>
                    <p style="font-size: 0.785rem; color: var(--zinc-500); margin: 0.15rem 0 0 0;">
                        Customize CRM background colors, ambient glows, system palettes, and typography modes in real-time.
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeModal('theme-palette-modal')" style="background: none; border: none; cursor: pointer; color: var(--zinc-400);">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>

        <!-- Body Scrollable -->
        <div style="padding: 1.5rem; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Section 1: Ambient Lighting Glow Presets -->
            <div>
                <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--zinc-900); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.45rem;">
                    <i data-lucide="sun-medium" style="width: 16px; height: 16px; color: #f59e0b;"></i>
                    1. Ambient Glow &amp; Atmosphere Presets (10 Styles)
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 0.75rem;">
                    <!-- Sunset -->
                    <div onclick="applyAmbientTheme('sunset');" style="padding: 0.85rem; border-radius: 12px; border: 1.5px solid #fdba74; background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); cursor: pointer; transition: transform 0.15s ease;" onmouseenter="this.style.transform='translateY(-2px)'" onmouseleave="this.style.transform='none'">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <strong style="font-size: 0.8125rem; color: #9a3412;">🌅 Sunset Honey-Gold</strong>
                            <span class="badge" style="background: #fed7aa; color: #7c2d12; font-size: 0.65rem;">Warm</span>
                        </div>
                        <p style="font-size: 0.725rem; color: #c2410c; margin: 0;">Golden hour ambiance with warm amber card borders and honey glow.</p>
                    </div>

                    <!-- Ocean -->
                    <div onclick="applyAmbientTheme('ocean');" style="padding: 0.85rem; border-radius: 12px; border: 1.5px solid #22d3ee; background: radial-gradient(circle at 50% 20%, #064e5c 0%, #022027 100%); cursor: pointer; color: #ecfeff; transition: transform 0.15s ease;" onmouseenter="this.style.transform='translateY(-2px)'" onmouseleave="this.style.transform='none'">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <strong style="font-size: 0.8125rem; color: #67e8f9;">🌊 Ocean Caustics</strong>
                            <span class="badge" style="background: #083344; color: #a5f3fc; font-size: 0.65rem;">Marine</span>
                        </div>
                        <p style="font-size: 0.725rem; color: #cffafe; margin: 0;">Deep marine teal caustics with rising aquatic cyan luminescence.</p>
                    </div>

                    <!-- Cyberpunk -->
                    <div onclick="applyAmbientTheme('cyberpunk');" style="padding: 0.85rem; border-radius: 12px; border: 1.5px solid #00f2fe; background: #0a0a0f; cursor: pointer; color: #f8fafc; transition: transform 0.15s ease;" onmouseenter="this.style.transform='translateY(-2px)'" onmouseleave="this.style.transform='none'">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <strong style="font-size: 0.8125rem; color: #00f2fe;">⚡ Cyberpunk Neon</strong>
                            <span class="badge" style="background: #18181b; color: #00f2fe; font-size: 0.65rem;">Laser</span>
                        </div>
                        <p style="font-size: 0.725rem; color: #94a3b8; margin: 0;">High-contrast cyber cyan borders with laser neon glow.</p>
                    </div>

                    <!-- Cosmic -->
                    <div onclick="applyAmbientTheme('cosmic');" style="padding: 0.85rem; border-radius: 12px; border: 1.5px solid #818cf8; background: #070913; cursor: pointer; color: #f8fafc; transition: transform 0.15s ease;" onmouseenter="this.style.transform='translateY(-2px)'" onmouseleave="this.style.transform='none'">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <strong style="font-size: 0.8125rem; color: #a5b4fc;">🌌 Deep Cosmic Galaxy</strong>
                            <span class="badge" style="background: #1e1b4b; color: #c7d2fe; font-size: 0.65rem;">Nebula</span>
                        </div>
                        <p style="font-size: 0.725rem; color: #94a3b8; margin: 0;">Deep galaxy indigo aura with starlight and purple nebula glow.</p>
                    </div>

                    <!-- Synthwave -->
                    <div onclick="applyAmbientTheme('synthwave');" style="padding: 0.85rem; border-radius: 12px; border: 1.5px solid #ff758f; background: linear-gradient(180deg, #18092a 0%, #2f0743 100%); cursor: pointer; color: #fdf2f8; transition: transform 0.15s ease;" onmouseenter="this.style.transform='translateY(-2px)'" onmouseleave="this.style.transform='none'">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <strong style="font-size: 0.8125rem; color: #fda4af;">📼 80s Retro Synthwave</strong>
                            <span class="badge" style="background: #4a044e; color: #fbcfe8; font-size: 0.65rem;">Retro</span>
                        </div>
                        <p style="font-size: 0.725rem; color: #e2e8f0; margin: 0;">Nostalgic retro twilight, horizon glow, and neon rose borders.</p>
                    </div>

                    <!-- Sakura -->
                    <div onclick="applyAmbientTheme('sakura');" style="padding: 0.85rem; border-radius: 12px; border: 1.5px solid #f43f5e; background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%); cursor: pointer; transition: transform 0.15s ease;" onmouseenter="this.style.transform='translateY(-2px)'" onmouseleave="this.style.transform='none'">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <strong style="font-size: 0.8125rem; color: #be123c;">🌸 Zen Sakura Blossom</strong>
                            <span class="badge" style="background: #fecdd3; color: #9f1239; font-size: 0.65rem;">Floral</span>
                        </div>
                        <p style="font-size: 0.725rem; color: #e11d48; margin: 0;">Gentle cherry blossom aura with soft ivory canvas and rose blush radiance.</p>
                    </div>

                    <!-- Matrix -->
                    <div onclick="applyAmbientTheme('matrix');" style="padding: 0.85rem; border-radius: 12px; border: 1.5px solid #16a34a; background: #030804; cursor: pointer; color: #dcfce7; transition: transform 0.15s ease;" onmouseenter="this.style.transform='translateY(-2px)'" onmouseleave="this.style.transform='none'">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <strong style="font-size: 0.8125rem; color: #4ade80;">💻 Matrix Green Digital</strong>
                            <span class="badge" style="background: #052e16; color: #86efac; font-size: 0.65rem;">Phosphor</span>
                        </div>
                        <p style="font-size: 0.725rem; color: #86efac; margin: 0;">Digital code luminescence with phosphorescent emerald glow.</p>
                    </div>

                    <!-- Pastel -->
                    <div onclick="applyAmbientTheme('pastel');" style="padding: 0.85rem; border-radius: 12px; border: 1.5px solid #f472b6; background: linear-gradient(120deg, #fbcfe8 0%, #e0e7ff 100%); cursor: pointer; transition: transform 0.15s ease;" onmouseenter="this.style.transform='translateY(-2px)'" onmouseleave="this.style.transform='none'">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <strong style="font-size: 0.8125rem; color: #9d174d;">☁️ Dreamy Pastel Iridescence</strong>
                            <span class="badge" style="background: #fce7f3; color: #831843; font-size: 0.65rem;">Pastel</span>
                        </div>
                        <p style="font-size: 0.725rem; color: #be185d; margin: 0;">Calming cotton candy clouds with iridescent lavender and mint bokeh.</p>
                    </div>

                    <!-- IT Hardware -->
                    <div onclick="applyAmbientTheme('it-hardware');" style="padding: 0.85rem; border-radius: 12px; border: 1.5px solid #38bdf8; background: #0b1120; cursor: pointer; color: #f8fafc; transition: transform 0.15s ease;" onmouseenter="this.style.transform='translateY(-2px)'" onmouseleave="this.style.transform='none'">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <strong style="font-size: 0.8125rem; color: #38bdf8;">🖥️ IT Hardware Cloud</strong>
                            <span class="badge" style="background: #082f49; color: #7dd3fc; font-size: 0.65rem;">Compute</span>
                        </div>
                        <p style="font-size: 0.725rem; color: #94a3b8; margin: 0;">Animated server silhouettes and hardware blueprint telemetry.</p>
                    </div>

                    <!-- Classic Professional -->
                    <div onclick="applyAmbientTheme('classic');" style="padding: 0.85rem; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; cursor: pointer; color: var(--zinc-900); transition: transform 0.15s ease;" onmouseenter="this.style.transform='translateY(-2px)'" onmouseleave="this.style.transform='none'">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <strong style="font-size: 0.8125rem; color: #0f172a;">💼 Classic DealSync (Default)</strong>
                            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 0.65rem;">Standard</span>
                        </div>
                        <p style="font-size: 0.725rem; color: #64748b; margin: 0;">Clean, standard enterprise slate with crisp zinc card borders.</p>
                    </div>
                </div>
            </div>

            <!-- Section 2: Custom Background Color & Canvas Swatches -->
            <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 14px; padding: 1.15rem;">
                <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--zinc-900); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.45rem;">
                    <i data-lucide="palette" style="width: 16px; height: 16px; color: var(--color-primary);"></i>
                    2. Custom CRM Canvas Background Color
                </h4>
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="color" id="crm-custom-bg-input" value="#efe6e6" onchange="applyPageBackground(this.value);" style="width: 44px; height: 40px; border: none; border-radius: 8px; cursor: pointer; padding: 2px;">
                        <div>
                            <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); display: block;">Pick Any Color</span>
                            <span style="font-size: 0.7rem; color: var(--zinc-400);">Instant live wallpaper</span>
                        </div>
                    </div>

                    <div style="height: 32px; width: 1px; background: var(--zinc-200);"></div>

                    <!-- Quick Preset Swatches -->
                    <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                        <span style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-right: 0.25rem;">Presets:</span>
                        <button type="button" onclick="applyPageBackground('#efe6e6');" style="width: 26px; height: 26px; border-radius: 6px; background: #efe6e6; border: 1.5px solid #d4c8c8; cursor: pointer;" title="Warm Blush (#efe6e6)"></button>
                        <button type="button" onclick="applyPageBackground('#f8fafc');" style="width: 26px; height: 26px; border-radius: 6px; background: #f8fafc; border: 1px solid #cbd5e1; cursor: pointer;" title="Slate Canvas"></button>
                        <button type="button" onclick="applyPageBackground('#f0f9ff');" style="width: 26px; height: 26px; border-radius: 6px; background: #f0f9ff; border: 1px solid #bae6fd; cursor: pointer;" title="Sky Tint"></button>
                        <button type="button" onclick="applyPageBackground('#f5f3ff');" style="width: 26px; height: 26px; border-radius: 6px; background: #f5f3ff; border: 1px solid #ddd6fe; cursor: pointer;" title="Indigo Mist"></button>
                        <button type="button" onclick="applyPageBackground('#f0fdf4');" style="width: 26px; height: 26px; border-radius: 6px; background: #f0fdf4; border: 1px solid #bbf7d0; cursor: pointer;" title="Mint Breeze"></button>
                        <button type="button" onclick="applyPageBackground('#fffbeb');" style="width: 26px; height: 26px; border-radius: 6px; background: #fffbeb; border: 1px solid #fde68a; cursor: pointer;" title="Warm Amber"></button>
                        <button type="button" onclick="applyPageBackground('#09090b');" style="width: 26px; height: 26px; border-radius: 6px; background: #09090b; border: 1px solid #27272a; cursor: pointer;" title="Obsidian Black"></button>
                        <button type="button" onclick="applyPageBackground('#18181b');" style="width: 26px; height: 26px; border-radius: 6px; background: #18181b; border: 1px solid #3f3f46; cursor: pointer;" title="Dark Charcoal"></button>
                    </div>
                </div>
            </div>

            <!-- Section 3: Accent Color & Design Systems -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <!-- Accent Color Picker -->
                <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 14px; padding: 1.15rem;">
                    <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--zinc-900); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.45rem;">
                        <i data-lucide="droplet" style="width: 16px; height: 16px; color: var(--color-primary);"></i>
                        3. Primary Accent Brand
                    </h4>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <button type="button" onclick="applyAccentColor('#4f46e5');" style="width: 30px; height: 30px; border-radius: 8px; background: #4f46e5; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="Indigo"></button>
                        <button type="button" onclick="applyAccentColor('#0284c7');" style="width: 30px; height: 30px; border-radius: 8px; background: #0284c7; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="Sky Blue"></button>
                        <button type="button" onclick="applyAccentColor('#1e40af');" style="width: 30px; height: 30px; border-radius: 8px; background: #1e40af; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="Revolut Cobalt"></button>
                        <button type="button" onclick="applyAccentColor('#059669');" style="width: 30px; height: 30px; border-radius: 8px; background: #059669; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="Emerald"></button>
                        <button type="button" onclick="applyAccentColor('#d97706');" style="width: 30px; height: 30px; border-radius: 8px; background: #d97706; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="Amber"></button>
                        <button type="button" onclick="applyAccentColor('#e11d48');" style="width: 30px; height: 30px; border-radius: 8px; background: #e11d48; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="Rose"></button>
                        <button type="button" onclick="applyAccentColor('#09090b');" style="width: 30px; height: 30px; border-radius: 8px; background: #09090b; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="Obsidian"></button>
                    </div>
                </div>

                <!-- Design System Switcher -->
                <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 14px; padding: 1.15rem;">
                    <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--zinc-900); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.45rem;">
                        <i data-lucide="layout" style="width: 16px; height: 16px; color: var(--color-primary);"></i>
                        4. Design System System
                    </h4>
                    <div style="display: flex; gap: 0.6rem;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="applyBranding('apple'); showToast('Design System: Apple Cupertino');" style="flex: 1; padding: 0.5rem;">
                            🍎 Apple Cupertino
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="applyBranding('revolut'); showToast('Design System: Revolut Fintech');" style="flex: 1; padding: 0.5rem;">
                            ⚡ Revolut Fintech
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 1.15rem 1.75rem; border-top: 1px solid var(--zinc-200); display: flex; align-items: center; justify-content: space-between; background: var(--zinc-50); flex-shrink: 0;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetThemeDefaults();">
                <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i>
                <span>Reset to Defaults</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="closeModal('theme-palette-modal'); showToast('Theme preferences saved!');">
                Done &amp; Close Studio
            </button>
        </div>
    </div>
</div>

<!-- User Profile & Preferences Modal -->
<div id="user-profile-modal" class="modal-overlay">
    <div class="modal-card" style="max-width: 680px; width: 95%;">
        <!-- Header -->
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--zinc-200); display: flex; align-items: center; justify-content: space-between; background: #ffffff;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--color-primary-subtle); color: var(--color-primary); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="user" style="width: 18px; height: 18px;"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-900); margin: 0; line-height: 1.2;">My Profile &amp; Preferences</h3>
                    <p style="font-size: 0.725rem; color: var(--zinc-500); margin: 0.15rem 0 0 0;">Manage personal identity, rich email signature, locale time zone, and active sessions.</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('user-profile-modal')" style="background: none; border: none; cursor: pointer; color: var(--zinc-400); padding: 0.25rem; border-radius: 6px;">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form onsubmit="event.preventDefault(); showToast('Profile settings saved successfully!'); closeModal('user-profile-modal');" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem; max-height: 70vh; overflow-y: auto;">
            <!-- Profile Identity Header -->
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--zinc-50); border-radius: 10px; border: 1px solid var(--zinc-200);">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: var(--zinc-900); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; font-weight: 700; flex-shrink: 0;">
                    AD
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 0.95rem; font-weight: 700; color: var(--zinc-900);">Admin User</div>
                    <div style="font-size: 0.775rem; color: var(--zinc-500);">admin@acme.com</div>
                    <div style="margin-top: 0.35rem; display: flex; gap: 0.4rem;">
                        <span class="badge badge-purple" style="font-size: 0.65rem;">System Administrator</span>
                        <span class="badge badge-emerald" style="font-size: 0.65rem;">Primary Tenant Owner</span>
                    </div>
                </div>
            </div>

            <!-- Fields Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Full Name *</label>
                    <input type="text" value="Admin User" required style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Email Address *</label>
                    <input type="email" value="admin@acme.com" required style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Job Title</label>
                    <input type="text" value="Chief Executive Officer / Superadmin" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Department</label>
                    <input type="text" value="Executive Leadership" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Phone Number</label>
                    <input type="text" value="+1 (555) 234-5678" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Time Zone</label>
                    <select style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem; background: #ffffff;">
                        <option value="America/New_York" selected>Eastern Time (US &amp; Canada) - GMT-4</option>
                        <option value="America/Chicago">Central Time (US &amp; Canada) - GMT-5</option>
                        <option value="America/Los_Angeles">Pacific Time (US &amp; Canada) - GMT-7</option>
                        <option value="Europe/London">London, Edinburgh - GMT+1</option>
                        <option value="Asia/Kolkata">Mumbai, New Delhi - GMT+5:30</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.35rem;">Default Email Signature</label>
                <textarea rows="3" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem; font-family: inherit;">Best regards,
Admin User | Acme Enterprise Tech
Confidentiality Notice: This message may contain proprietary information.</textarea>
            </div>

            <!-- Footer Buttons -->
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; padding-top: 0.5rem; border-top: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('user-profile-modal')">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="save" style="width: 14px; height: 14px;"></i>
                    <span>Save Profile Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

