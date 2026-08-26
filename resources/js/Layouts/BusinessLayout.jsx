import { useState } from 'react';

import Sidebar from '../Components/Business/Sidebar';
import Topbar from '../Components/Business/Topbar';

export default function BusinessLayout({ children }) {

    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="rr-business-layout">

            <Sidebar
                open={sidebarOpen}
                onClose={() => setSidebarOpen(false)}
            />

            <div className="rr-business-main">

                <Topbar
                    onMenuClick={() => setSidebarOpen(true)}
                />

                <main className="rr-business-content">

                    {children}

                </main>

            </div>

        </div>
    );
}