<!-- =========================================================================
     SLIDE-OVER DRAWER: DRILLDOWN RECORDS (Live Preview Click)
     ========================================================================= -->
<div id="drawer-drilldown" class="modal-overlay" style="display: none; background: rgba(0,0,0,0.35); justify-content: flex-end;" onclick="closeDrilldownDrawer()">
    <div style="width: 520px; max-width: 100%; height: 100%; background: #ffffff; box-shadow: -4px 0 25px rgba(0,0,0,0.15); display: flex; flex-direction: column; overflow: hidden; animation: slideInRight 0.25s ease;" onclick="event.stopPropagation()">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--zinc-200); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div>
                <span id="drawer-category-badge" style="font-size: 0.6875rem; font-weight: 700; color: #4f46e5; background: #e0e7ff; padding: 2px 8px; border-radius: 9999px;">
                    Telemetry Drilldown
                </span>
                <h3 id="drawer-title" style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0.35rem 0 0 0;">
                    Contributing Deal Records
                </h3>
            </div>
            <button type="button" onclick="closeDrilldownDrawer()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <div style="padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 1.25rem;">
            <!-- Summary Metric Card -->
            <div style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: #ffffff; border-radius: 12px; padding: 1.25rem;">
                <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #c7d2fe;">Active Cohort Total</span>
                <div id="drawer-metric-val" style="font-size: 1.85rem; font-weight: 900; margin: 0.25rem 0;">$48,600,000</div>
                <p id="drawer-desc" style="font-size: 0.75rem; color: #e0e7ff; margin: 0;">Verified CRM production database records pacing toward annual targets.</p>
            </div>

            <!-- Records Table -->
            <div>
                <h4 style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem 0;">
                    Contributing Enterprise Records (Top 5)
                </h4>
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem; text-align: left;">
                        <thead style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">
                            <tr>
                                <th style="padding: 6px 10px; font-weight: 700; color: #475569;">Deal / Account</th>
                                <th style="padding: 6px 10px; font-weight: 700; color: #475569;">ARR</th>
                                <th style="padding: 6px 10px; font-weight: 700; color: #475569;">Stage</th>
                                <th style="padding: 6px 10px; font-weight: 700; color: #475569;">Owner</th>
                            </tr>
                        </thead>
                        <tbody style="divide-y divide-slate-100;">
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 10px; font-weight: 600; color: #0f172a;">Apex Global Cloud</td>
                                <td style="padding: 8px 10px; color: #059669; font-weight: 700;">$1,850,000</td>
                                <td style="padding: 8px 10px;"><span style="background: #dbeafe; color: #1e40af; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Negotiation</span></td>
                                <td style="padding: 8px 10px; color: #475569;">Sarah Miller</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 10px; font-weight: 600; color: #0f172a;">Vertex BioTech Hub</td>
                                <td style="padding: 8px 10px; color: #059669; font-weight: 700;">$940,000</td>
                                <td style="padding: 8px 10px;"><span style="background: #fef3c7; color: #92400e; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Proposal</span></td>
                                <td style="padding: 8px 10px; color: #475569;">Alex Rivera</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 10px; font-weight: 600; color: #0f172a;">OmniCorp Systems</td>
                                <td style="padding: 8px 10px; color: #059669; font-weight: 700;">$780,000</td>
                                <td style="padding: 8px 10px;"><span style="background: #dcfce7; color: #166534; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Closed Won</span></td>
                                <td style="padding: 8px 10px; color: #475569;">David Vance</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 10px; font-weight: 600; color: #0f172a;">Helios Energy Corp</td>
                                <td style="padding: 8px 10px; color: #059669; font-weight: 700;">$620,000</td>
                                <td style="padding: 8px 10px;"><span style="background: #e0e7ff; color: #3730a3; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Discovery</span></td>
                                <td style="padding: 8px 10px; color: #475569;">Elena Rostova</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--zinc-200); background: #f8fafc; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeDrilldownDrawer()" class="btn btn-secondary">
                Close Inspector
            </button>
        </div>
    </div>
</div>
