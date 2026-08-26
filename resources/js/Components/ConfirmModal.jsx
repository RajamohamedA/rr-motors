export default function ConfirmModal({
    show,
    title = 'Confirm Action',
    message,
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    icon = 'bi-exclamation-triangle',
    variant = 'danger',
    processing = false,
    confirmDisabled = false,
    onConfirm,
    onCancel,
}) {

    if (!show) {
        return null;
    }

    const isDanger =
        variant === 'danger';

    return (
        <>
            {/* Backdrop */}

            <div className="rr-modal-backdrop"></div>


            {/* Modal */}

            <div
                className="rr-modal-container"
                role="dialog"
                aria-modal="true"
            >

                <div className="rr-confirm-modal">

                    {/* Header */}

                    <div className="rr-confirm-header">

                        <div
                            className={
                                isDanger
                                    ? 'rr-confirm-icon rr-confirm-icon-danger'
                                    : 'rr-confirm-icon'
                            }
                        >
                            <i className={`bi ${icon}`}></i>
                        </div>


                        <div className="rr-confirm-heading">

                            <div className="rr-confirm-title">
                                {title}
                            </div>

                            <div className="rr-confirm-subtitle">
                                Please confirm before continuing
                            </div>

                        </div>


                        <button
                            type="button"
                            className="rr-confirm-close"
                            onClick={onCancel}
                            disabled={processing}
                            aria-label="Close"
                        >
                            <i className="bi bi-x-lg"></i>
                        </button>

                    </div>


                    {/* Body */}

                    <div className="rr-confirm-body">

                        <p>
                            {message}
                        </p>


                        {isDanger && (

                            <div className="rr-confirm-warning">

                                <i className="bi bi-info-circle"></i>

                                <span>
                                    This action may affect access to this record.
                                </span>

                            </div>

                        )}

                    </div>


                    {/* Footer */}

                    <div className="rr-confirm-footer">

                        <button
                            type="button"
                            className="rr-btn rr-btn-secondary"
                            onClick={onCancel}
                            disabled={processing}
                        >
                            {cancelText}
                        </button>


                        <button
                            type="button"
                            className={
                                isDanger
                                    ? 'rr-btn rr-btn-danger-solid'
                                    : 'rr-btn rr-btn-primary'
                            }
                            onClick={onConfirm}
                            disabled={
                                processing ||
                                confirmDisabled
                            }
                        >

                            {processing ? (

                                <>
                                    <span className="spinner-border spinner-border-sm"></span>

                                    Processing...
                                </>

                            ) : (

                                <>
                                    <i className={`bi ${icon}`}></i>

                                    {confirmText}
                                </>

                            )}

                        </button>

                    </div>

                </div>

            </div>
        </>
    );
}