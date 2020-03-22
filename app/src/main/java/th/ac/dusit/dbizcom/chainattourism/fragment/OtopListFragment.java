package th.ac.dusit.dbizcom.chainattourism.fragment;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.DialogInterface;
import android.graphics.Rect;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.widget.CircularProgressDrawable;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.DisplayMetrics;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import com.bumptech.glide.Glide;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import retrofit2.Call;
import retrofit2.Retrofit;
import th.ac.dusit.dbizcom.chainattourism.R;
import th.ac.dusit.dbizcom.chainattourism.etc.Utils;
import th.ac.dusit.dbizcom.chainattourism.model.Otop;
import th.ac.dusit.dbizcom.chainattourism.net.ApiClient;
import th.ac.dusit.dbizcom.chainattourism.net.GetOtopResponse;
import th.ac.dusit.dbizcom.chainattourism.net.MyRetrofitCallback;
import th.ac.dusit.dbizcom.chainattourism.net.WebServices;

public class OtopListFragment extends Fragment {

    private static final String ARG_DISTRICT_NAME = "district_name";
    private static final String ARG_SEARCH_TERM = "search_term";

    private String mDistrictName;
    private String mSearchTerm;
    private List<Otop> mOtopList = null;

    private OtopListFragmentListener mListener;

    private View mProgressView;
    private RecyclerView mOtopListRecyclerView;

    public OtopListFragment() {
        // Required empty public constructor
    }

    public static OtopListFragment newInstance(String param, boolean isSearch) {
        OtopListFragment fragment = new OtopListFragment();
        Bundle args = new Bundle();
        if (isSearch) {
            args.putString(ARG_SEARCH_TERM, param);
        } else {
            args.putString(ARG_DISTRICT_NAME, param);
        }
        fragment.setArguments(args);
        return fragment;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getArguments() != null) {
            mDistrictName = getArguments().getString(ARG_DISTRICT_NAME);
            mSearchTerm = getArguments().getString(ARG_SEARCH_TERM);
        }
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_otop_list, container, false);
    }

    @SuppressLint("SetTextI18n")
    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        TextView titleTextView = view.findViewById(R.id.title_text_view);
        if (mDistrictName != null) {
            titleTextView.setText("สินค้า OTOP");
        } else {
            titleTextView.setText("ผลการค้นหาสินค้า OTOP");
        }

        mOtopListRecyclerView = view.findViewById(R.id.otop_list_recycler_view);
        mProgressView = view.findViewById(R.id.progress_view);

        if (mOtopList == null) {
            doGetOtop();
        } else {
            setupRecyclerView();
        }
    }

    private void doGetOtop() {
        mProgressView.setVisibility(View.VISIBLE);

        Retrofit retrofit = ApiClient.getClient();
        WebServices services = retrofit.create(WebServices.class);

        Call<GetOtopResponse> call;
        if (mDistrictName != null) {
            call = services.getOtopByDistrict(mDistrictName);
        } else {
            call = services.searchOtop(mSearchTerm);
        }
        call.enqueue(new MyRetrofitCallback<>(
                getActivity(),
                null,
                mProgressView,
                new MyRetrofitCallback.MyRetrofitCallbackListener<GetOtopResponse>() {
                    @Override
                    public void onSuccess(GetOtopResponse responseBody) {
                        mOtopList = responseBody.otopList;
                        if (mDistrictName != null) {
                            mOtopList = insertSubDistrictHeader(mOtopList);
                        }
                        setupRecyclerView();
                    }

                    @Override
                    public void onError(String errorMessage) {
                        if (getActivity() != null) {
                            Utils.showOkDialog(getActivity(), "Error", errorMessage, new DialogInterface.OnClickListener() {
                                @Override
                                public void onClick(DialogInterface dialog, int which) {

                                }
                            });
                        }
                    }
                }
        ));
    }

    private List<Otop> insertSubDistrictHeader(List<Otop> otopList) {
        List<Otop> newOtopList = new ArrayList<>();
        String previousSubDistrict = null;

        for (Otop otop : otopList) {
            if (previousSubDistrict == null || !previousSubDistrict.equals(otop.subDistrict)) {
                newOtopList.add(new Otop(0, null, mDistrictName, otop.subDistrict, null, null, null, 0, null, null, null, 0, 0, null, null, false, null, 0, 0));
                previousSubDistrict = otop.subDistrict;
            }
            newOtopList.add(otop);
        }

        return newOtopList;
    }

    private void setupRecyclerView() {
        if (getContext() != null) {
            OtopListAdapter adapter = new OtopListAdapter(
                    getContext(),
                    mOtopList,
                    mListener
            );
            mOtopListRecyclerView.setLayoutManager(new LinearLayoutManager(getContext()));
            mOtopListRecyclerView.addItemDecoration(new SpacingDecoration(getContext()));
            mOtopListRecyclerView.setAdapter(adapter);
        }
    }

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
        if (context instanceof OtopListFragmentListener) {
            mListener = (OtopListFragmentListener) context;
        } else {
            throw new RuntimeException(context.toString()
                    + " must implement OtopListFragmentListener");
        }
    }

    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    public interface OtopListFragmentListener {
        void onClickOtop(Otop otop);
    }

    private static class OtopListAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

        private static int TYPE_HEADER = 1;
        private static int TYPE_NORMAL = 2;

        private final Context mContext;
        private final List<Otop> mOtopList;
        private final OtopListFragmentListener mListener;

        OtopListAdapter(Context context, List<Otop> otopList, OtopListFragmentListener listener) {
            mContext = context;
            mOtopList = otopList;
            mListener = listener;
        }

        @Override
        public int getItemViewType(int position) {
            if (mOtopList.get(position).id == 0) {
                return TYPE_HEADER;
            } else {
                return TYPE_NORMAL;
            }
        }

        @NonNull
        @Override
        public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view;

            if (viewType == TYPE_HEADER) {
                view = LayoutInflater.from(parent.getContext()).inflate(
                        R.layout.item_otop_header, parent, false
                );
                return new HeaderViewHolder(view);
            } else {
                view = LayoutInflater.from(parent.getContext()).inflate(
                        R.layout.item_otop, parent, false
                );
                return new NormalViewHolder(view);
            }
        }

        @Override
        public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
            final Otop otop = mOtopList.get(position);

            if (getItemViewType(position) == TYPE_HEADER) {
                HeaderViewHolder h = (HeaderViewHolder) holder;
                h.mOtop = otop;
                h.mSubDistrictTextView.setText("ตำบล".concat(otop.subDistrict));
            } else {
                NormalViewHolder h = (NormalViewHolder) holder;
                h.mOtop = otop;
                h.mOtopNameTextView.setText(otop.name);
                h.mVillageTextView.setText(otop.village);

                CircularProgressDrawable circularProgressDrawable = new CircularProgressDrawable(mContext);
                circularProgressDrawable.setStrokeWidth(5f);
                circularProgressDrawable.setCenterRadius(30f);
                circularProgressDrawable.start();

                if (otop.listImage != null) {
                    Glide.with(mContext)
                            .load(ApiClient.IMAGE_BASE_URL.concat(otop.listImage))
                            .placeholder(circularProgressDrawable)
                            .into(h.mOtopImageView);
                }
            }
        }

        @Override
        public int getItemCount() {
            return mOtopList.size();
        }

        class NormalViewHolder extends RecyclerView.ViewHolder {

            private final View mRootView;
            private final TextView mOtopNameTextView;
            private final TextView mVillageTextView;
            private final ImageView mOtopImageView;

            private Otop mOtop;

            NormalViewHolder(View itemView) {
                super(itemView);

                mRootView = itemView;
                mOtopNameTextView = itemView.findViewById(R.id.otop_name_text_view);
                mVillageTextView = itemView.findViewById(R.id.village_text_view);
                mOtopImageView = itemView.findViewById(R.id.otop_image_view);

                mRootView.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        mListener.onClickOtop(mOtop);
                    }
                });
            }
        }

        class HeaderViewHolder extends RecyclerView.ViewHolder {

            private final View mRootView;
            private final TextView mSubDistrictTextView;

            private Otop mOtop;

            HeaderViewHolder(View itemView) {
                super(itemView);

                mRootView = itemView;
                mSubDistrictTextView = itemView.findViewById(R.id.sub_district_text_view);

                mRootView.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        String msg = String.format(
                                Locale.getDefault(),
                                "ต.%s อ.%s จ.ชัยนาท",
                                mOtop.subDistrict, mOtop.district
                        );
                        Toast.makeText(mContext, msg, Toast.LENGTH_LONG).show();
                    }
                });
            }
        }
    }

    public class SpacingDecoration extends RecyclerView.ItemDecoration {

        private final static int MARGIN_TOP_IN_DP = 0;
        private final static int MARGIN_BOTTOM_IN_DP = 16;
        private final int mMarginTop, mMarginBottom;

        SpacingDecoration(@NonNull Context context) {
            DisplayMetrics metrics = context.getResources().getDisplayMetrics();
            mMarginTop = (int) TypedValue.applyDimension(
                    TypedValue.COMPLEX_UNIT_DIP,
                    MARGIN_TOP_IN_DP,
                    metrics
            );
            mMarginBottom = (int) TypedValue.applyDimension(
                    TypedValue.COMPLEX_UNIT_DIP,
                    MARGIN_BOTTOM_IN_DP,
                    metrics
            );
        }

        @Override
        public void getItemOffsets(@NonNull Rect outRect, @NonNull View view,
                                   @NonNull RecyclerView parent,
                                   @NonNull RecyclerView.State state) {
            final int itemPosition = parent.getChildAdapterPosition(view);
            if (itemPosition == RecyclerView.NO_POSITION) {
                return;
            }
            if (itemPosition == 0) {
                outRect.top = mMarginTop;
                //outRect.top = 0;
            }
            final RecyclerView.Adapter adapter = parent.getAdapter();
            if ((adapter != null) && (itemPosition == adapter.getItemCount() - 1)) {
                outRect.bottom = mMarginBottom;
            }
        }
    }
}
